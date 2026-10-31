<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\CustomerProfile;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Create a new recurring subscription.
     */
    public function createSubscription(
        CustomerProfile $customer,
        int $addressId,
        int $slotId,
        string $frequency,
        array $items, // [['product_id' => 1, 'quantity' => 2], ...]
        string $startDate,
        ?string $endDate = null,
        array $customDays = [],
        string $paymentMethod = 'cod',
        ?string $notes = null
    ): Subscription {
        if (empty($items)) {
            throw new InvalidArgumentException('Subscription must contain at least one water can product.');
        }

        $address = CustomerAddress::where('id', $addressId)
            ->where('customer_profile_id', $customer->id)
            ->firstOrFail();

        $slot = DeliverySlot::findOrFail($slotId);

        $parsedStart = Carbon::parse($startDate);
        $parsedEnd = $endDate ? Carbon::parse($endDate) : null;

        if ($parsedEnd && $parsedEnd->lt($parsedStart)) {
            throw new InvalidArgumentException('Subscription end date cannot be earlier than start date.');
        }

        return DB::transaction(function () use (
            $customer, $address, $slot, $frequency, $items, $parsedStart, $parsedEnd, $customDays, $paymentMethod, $notes
        ) {
            $year = date('Y');
            $latest = Subscription::where('subscription_number', 'LIKE', "SUB-{$year}-%")->latest('id')->first();
            $seq = $latest ? ((int) substr($latest->subscription_number, -4)) + 1 : 1;
            $subNumber = sprintf("SUB-%s-%04d", $year, $seq);

            $subscription = Subscription::create([
                'subscription_number' => $subNumber,
                'customer_profile_id' => $customer->id,
                'branch_id' => $customer->branch_id,
                'delivery_address_id' => $address->id,
                'delivery_slot_id' => $slot->id,
                'frequency' => $frequency,
                'custom_days' => $frequency === 'custom_days' ? $customDays : null,
                'status' => 'active',
                'start_date' => $parsedStart->format('Y-m-d'),
                'end_date' => $parsedEnd?->format('Y-m-d'),
                'next_delivery_date' => $parsedStart->format('Y-m-d'),
                'payment_method' => $paymentMethod,
                'customer_notes' => $notes,
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                SubscriptionItem::create([
                    'subscription_id' => $subscription->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $product->selling_price,
                ]);
            }

            return $subscription->load(['items.product', 'deliveryAddress', 'deliverySlot']);
        });
    }

    /**
     * Compute next delivery date based on recurring cadence.
     */
    public function calculateNextDeliveryDate(string $frequency, Carbon $fromDate, array $customDays = []): Carbon
    {
        return match ($frequency) {
            'daily' => $fromDate->copy()->addDay(),
            'alternate_days' => $fromDate->copy()->addDays(2),
            'weekly' => $fromDate->copy()->addWeek(),
            'monthly' => $fromDate->copy()->addMonth(),
            'custom_days' => $this->findNextCustomDay($fromDate, $customDays),
            default => $fromDate->copy()->addDay(),
        };
    }

    /**
     * Helper to find next occurrence for custom delivery days (e.g. ['mon', 'wed', 'fri']).
     */
    protected function findNextCustomDay(Carbon $fromDate, array $customDays): Carbon
    {
        if (empty($customDays)) {
            return $fromDate->copy()->addDay();
        }

        $normalized = array_map(fn($d) => strtolower(substr($d, 0, 3)), $customDays);

        for ($i = 1; $i <= 7; $i++) {
            $next = $fromDate->copy()->addDays($i);
            $dayShort = strtolower($next->format('D'));
            if (in_array($dayShort, $normalized)) {
                return $next;
            }
        }

        return $fromDate->copy()->addDay();
    }

    /**
     * Automatically generate orders for all active subscriptions due on or before target date.
     */
    public function generateScheduledOrders(string $targetDate): int
    {
        $target = Carbon::parse($targetDate)->format('Y-m-d');
        $generatedCount = 0;

        $subscriptions = Subscription::with(['items.product.canType', 'customer.user', 'deliveryAddress'])
            ->where('status', 'active')
            ->whereDate('next_delivery_date', '<=', $target)
            ->where(function ($q) use ($target) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $target);
            })
            ->get();

        foreach ($subscriptions as $sub) {
            DB::transaction(function () use ($sub, $target, &$generatedCount) {
                $deliveryDate = $sub->next_delivery_date->format('Y-m-d');

                // Generate Sequential Order Number
                $datePrefix = Carbon::parse($deliveryDate)->format('Ymd');
                $latest = Order::where('order_number', 'LIKE', "ORD-{$datePrefix}-%")->latest('id')->first();
                $seq = $latest ? ((int) substr($latest->order_number, -4)) + 1 : 1;
                $orderNumber = sprintf("ORD-%s-%04d", $datePrefix, $seq);

                $addr = $sub->deliveryAddress;
                $subtotal = 0.00;
                $totalTax = 0.00;

                foreach ($sub->items as $item) {
                    $line = (float) ($item->quantity * $item->unit_price);
                    $subtotal += $line;
                    $taxRate = (float) ($item->product?->tax_rate_percent ?? 0);
                    $totalTax += round($line * ($taxRate / 100), 2);
                }

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'idempotency_key' => "SUB-ORD-{$sub->id}-" . uniqid(),
                    'customer_profile_id' => $sub->customer_profile_id,
                    'branch_id' => $sub->branch_id,
                    'delivery_address_id' => $addr->id,
                    'snapshot_address' => [
                        'contact_name' => $addr->contact_person_name,
                        'contact_mobile' => $addr->contact_person_mobile,
                        'house_flat_no' => $addr->house_flat_no,
                        'building' => $addr->building_apartment_name,
                        'street' => $addr->street_address,
                        'area' => $addr->area,
                        'city' => $addr->city,
                        'state' => $addr->state,
                        'pincode' => $addr->pincode,
                        'instructions' => $addr->delivery_instructions,
                    ],
                    'order_status' => 'confirmed',
                    'fulfillment_status' => 'unfulfilled',
                    'payment_status' => 'unpaid',
                    'delivery_status' => 'pending',
                    'subtotal_amount' => $subtotal,
                    'tax_amount' => $totalTax,
                    'delivery_charge' => 0.00,
                    'discount_amount' => 0.00,
                    'total_amount' => $subtotal,
                    'paid_amount' => 0.00,
                    'payment_method_preferred' => $sub->payment_method,
                    'delivery_date' => $deliveryDate,
                    'delivery_slot_id' => $sub->delivery_slot_id,
                    'customer_notes' => $sub->customer_notes,
                    'is_subscription_order' => true,
                    'subscription_id' => $sub->id,
                ]);

                foreach ($sub->items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_name_snapshot' => $item->product?->name ?? 'Water Can',
                        'can_type_id' => $item->product?->can_type_id,
                        'quantity' => $item->quantity,
                        'delivered_quantity' => 0,
                        'unit_price' => $item->unit_price,
                        'tax_rate_percent' => $item->product?->tax_rate_percent ?? 0,
                        'tax_amount' => 0.00,
                        'subtotal' => (float) ($item->quantity * $item->unit_price),
                    ]);
                }

                // If Pay Later, debit customer ledger
                if ($sub->payment_method === 'pay_later') {
                    $this->paymentService->recordOrderCharge($order);
                }

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => 'confirmed',
                    'comment' => "Recurring subscription order auto-generated from #{$sub->subscription_number}",
                    'changed_by_user_id' => null,
                ]);

                // Calculate next cycle date
                $nextDate = $this->calculateNextDeliveryDate(
                    $sub->frequency,
                    Carbon::parse($deliveryDate),
                    $sub->custom_days ?? []
                );

                $newStatus = ($sub->end_date && $nextDate->gt($sub->end_date)) ? 'completed' : 'active';

                $sub->update([
                    'last_generated_date' => $deliveryDate,
                    'next_delivery_date' => $nextDate->format('Y-m-d'),
                    'status' => $newStatus,
                ]);

                $generatedCount++;
            });
        }

        return $generatedCount;
    }

    /**
     * Pause a subscription.
     */
    public function pauseSubscription(Subscription $sub, ?string $reason = null): void
    {
        $sub->update([
            'status' => 'paused',
            'pause_reason' => $reason,
        ]);
    }

    /**
     * Resume a paused subscription.
     */
    public function resumeSubscription(Subscription $sub): void
    {
        $today = Carbon::today();
        $nextDate = $sub->next_delivery_date;

        if ($nextDate->lt($today)) {
            $nextDate = $today;
        }

        $sub->update([
            'status' => 'active',
            'pause_reason' => null,
            'next_delivery_date' => $nextDate->format('Y-m-d'),
        ]);
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(Subscription $sub, ?string $reason = null): void
    {
        $sub->update([
            'status' => 'cancelled',
            'pause_reason' => $reason,
        ]);
    }
}
