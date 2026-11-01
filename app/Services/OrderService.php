<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\DeliveryPincode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected DeliverySlotService $slotService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Complete atomic order placement transaction from active cart
     */
    public function createOrderFromCart(
        User $user,
        Cart $cart,
        int $addressId,
        string $deliveryDate,
        int $slotId,
        string $paymentMethod,
        ?string $customerNotes = null,
        ?string $idempotencyKey = null,
        ?bool $hasEmptyCansToExchange = null
    ): Order {
        // 1. Idempotency Check
        $idempotencyKey = $idempotencyKey ?: 'IDEMP_' . $user->id . '_' . time() . '_' . rand(1000, 9999);
        $existingOrder = Order::where('idempotency_key', $idempotencyKey)->first();
        if ($existingOrder) {
            return $existingOrder;
        }

        // 2. Validate Cart State
        if ($cart->items->isEmpty()) {
            throw new InvalidArgumentException('Your cart is empty. Please add water cans before checking out.');
        }

        // 3. Validate Customer Profile
        $profile = $user->customerProfile;
        if (! $profile) {
            throw new InvalidArgumentException('Customer profile not found for this account.');
        }

        // 4. Validate Delivery Address & Pincode Serviceability
        $address = CustomerAddress::where('id', $addressId)
            ->where('customer_profile_id', $profile->id)
            ->where('is_active', true)
            ->firstOrFail();

        $pincodeRecord = DeliveryPincode::where('pincode', $address->pincode)
            ->where('is_serviceable', true)
            ->first();

        if (! $pincodeRecord) {
            throw new InvalidArgumentException("Delivery is currently not available for address pincode {$address->pincode}.");
        }

        // 5. Calculate Server-Side Subtotal, Litres, Deposit & Delivery Fee
        $hasEmptyCansToExchange = $hasEmptyCansToExchange !== null
            ? (bool) $hasEmptyCansToExchange
            : (bool) ($cart->has_empty_cans_to_exchange ?? true);

        $subtotal = 0.00;
        $taxTotal = 0.00;
        $totalLitres = 0.00;
        $depositAmount = 0.00;
        $exchangeableCount = 0;

        foreach ($cart->items as $item) {
            $product = $item->product;
            $lineSubtotal = (float) ($item->quantity * $product->selling_price);
            $subtotal += $lineSubtotal;

            $taxRate = (float) $product->tax_rate_percent;
            $taxAmount = round($lineSubtotal * ($taxRate / 100), 2);
            $taxTotal += $taxAmount;

            $totalLitres += ($item->quantity * (float) $product->capacity_value);

            if ($product->requires_can_exchange) {
                $exchangeableCount += $item->quantity;
                if (! $hasEmptyCansToExchange) {
                    $canDeposit = (float) ($product->canType?->deposit_amount ?? 150.00);
                    $depositAmount += ($item->quantity * $canDeposit);
                }
            }
        }

        $emptyCansExpected = $hasEmptyCansToExchange ? $exchangeableCount : 0;

        // Delivery fee based on zone threshold
        $deliveryCharge = ($subtotal >= (float) $pincodeRecord->min_order_amount)
            ? 0.00
            : (float) $pincodeRecord->delivery_charge;

        $totalPayable = $subtotal + $deliveryCharge + $depositAmount;

        // 6. Validate Pay Later Eligibility & Credit Limits
        if ($paymentMethod === 'pay_later') {
            if (! $profile->allow_pay_later) {
                throw new InvalidArgumentException('Your account is not approved for Pay Later credit. Please select Online Payment or COD.');
            }

            $currentDue = (float) $profile->outstanding_financial_balance;
            $creditLimit = (float) $profile->credit_limit;

            if (($currentDue + $totalPayable) > $creditLimit) {
                throw new InvalidArgumentException("Order exceeds your approved credit limit of ₹{$creditLimit}. Please settle your past balance or select Online Payment.");
            }
        }

        // 7. Atomic Database Transaction: Capacity Reservation + Order Persist + Cart Clear
        return DB::transaction(function () use (
            $user, $profile, $cart, $address, $deliveryDate, $slotId, 
            $paymentMethod, $customerNotes, $idempotencyKey, 
            $subtotal, $taxTotal, $deliveryCharge, $depositAmount, $totalPayable, $totalLitres,
            $hasEmptyCansToExchange, $emptyCansExpected
        ) {
            // A. Atomically Reserve Slot Capacity
            $this->slotService->reserveCapacity($slotId, $deliveryDate, $totalLitres);

            // B. Generate Sequential Order Number: ORD-YYYYMMDD-XXXX
            $datePrefix = Carbon::now()->format('Ymd');
            $latestOrder = Order::where('order_number', 'LIKE', "ORD-{$datePrefix}-%")->latest('id')->first();
            $seq = $latestOrder ? ((int) substr($latestOrder->order_number, -4)) + 1 : 1;
            $orderNumber = sprintf("ORD-%s-%04d", $datePrefix, $seq);

            // C. Determine Initial Statuses
            $initialOrderStatus = ($paymentMethod === 'razorpay') ? 'pending' : 'confirmed';
            $initialPaymentStatus = 'unpaid';

            // D. Create Order Record
            $order = Order::create([
                'order_number' => $orderNumber,
                'idempotency_key' => $idempotencyKey,
                'customer_profile_id' => $profile->id,
                'branch_id' => $profile->branch_id,
                'delivery_address_id' => $address->id,
                'snapshot_address' => [
                    'contact_name' => $address->contact_person_name,
                    'contact_mobile' => $address->contact_person_mobile,
                    'house_flat_no' => $address->house_flat_no,
                    'building' => $address->building_apartment_name,
                    'street' => $address->street_address,
                    'landmark' => $address->landmark,
                    'area' => $address->area,
                    'city' => $address->city,
                    'state' => $address->state,
                    'pincode' => $address->pincode,
                    'instructions' => $address->delivery_instructions,
                ],
                'order_status' => $initialOrderStatus,
                'fulfillment_status' => 'unfulfilled',
                'payment_status' => $initialPaymentStatus,
                'delivery_status' => 'pending',
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxTotal,
                'delivery_charge' => $deliveryCharge,
                'discount_amount' => 0.00,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalPayable,
                'paid_amount' => 0.00,
                'previous_outstanding_at_order' => (float) $profile->outstanding_financial_balance,
                'payment_method_preferred' => $paymentMethod,
                'has_empty_cans_to_exchange' => $hasEmptyCansToExchange,
                'empty_cans_expected' => $emptyCansExpected,
                'delivery_date' => Carbon::parse($deliveryDate)->format('Y-m-d'),
                'delivery_slot_id' => $slotId,
                'customer_notes' => $customerNotes,
                'is_subscription_order' => false,
            ]);

            // E. Create Order Items with Immutable Snapshots
            foreach ($cart->items as $item) {
                $product = $item->product;
                $lineSubtotal = (float) ($item->quantity * $product->selling_price);
                $taxAmount = round($lineSubtotal * ((float) $product->tax_rate_percent / 100), 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'can_type_id' => $product->can_type_id,
                    'quantity' => $item->quantity,
                    'delivered_quantity' => 0,
                    'unit_price' => $product->selling_price,
                    'tax_rate_percent' => $product->tax_rate_percent,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            // F. Log Initial Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $initialOrderStatus,
                'comment' => "Order placed via {$paymentMethod}",
                'changed_by_user_id' => $user->id,
            ]);

            // G. Record Customer Credit Debit if Pay Later
            if ($paymentMethod === 'pay_later') {
                $this->paymentService->recordOrderCharge($order);
            }

            // H. Clear Cart
            $cart->items()->delete();

            return $order->load(['items', 'deliverySlot', 'customer']);
        });
    }
}
