<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\DeliveryPincode;
use App\Models\Order;
use App\Services\CartService;
use App\Services\DeliverySlotService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected DeliverySlotService $slotService,
        protected OrderService $orderService
    ) {}

    /**
     * Display checkout screen.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $branchId = session('active_branch_id', 1);

        $cart = $this->cartService->getOrCreateCart($user, $request->session()->getId(), $branchId);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty. Please select products to continue.']);
        }

        $profile = $user->customerProfile;
        if (! $profile) {
            $profile = \App\Models\CustomerProfile::create([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'customer_type' => 'residential',
                'credit_limit' => 1000.00,
                'outstanding_financial_balance' => 0.00,
                'allow_pay_later' => true,
            ]);
        }

        $addresses = CustomerAddress::where('customer_profile_id', $profile->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get();

        // 7 Delivery Dates (Today + next 6 days)
        $deliveryDates = [];
        $start = Carbon::today();
        for ($i = 0; $i < 7; $i++) {
            $d = $start->copy()->addDays($i);
            $deliveryDates[] = [
                'date' => $d->format('Y-m-d'),
                'label' => $i === 0 ? 'Today (' . $d->format('D, d M') . ')' : ($i === 1 ? 'Tomorrow (' . $d->format('D, d M') . ')' : $d->format('D, d M')),
            ];
        }

        $initialDate = $deliveryDates[0]['date'];
        $totalLitres = $cart->calculateTotalLitres();
        $slots = $this->slotService->getAvailableSlots($branchId, $initialDate, $totalLitres);

        // If today has no open slots (e.g. evening cutoff passed), auto-select first date that has open slots
        if ($slots->isEmpty()) {
            foreach ($deliveryDates as $d) {
                $checkSlots = $this->slotService->getAvailableSlots($branchId, $d['date'], $totalLitres);
                if ($checkSlots->isNotEmpty()) {
                    $initialDate = $d['date'];
                    $slots = $checkSlots;
                    break;
                }
            }
        }

        $subtotal = $cart->calculateSubtotal();
        $depositAmount = $cart->calculateDepositAmount();
        $exchangeableCount = $cart->totalExchangeableCansCount();

        // Determine estimated delivery charge based on default address
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        $deliveryCharge = 0.00;
        if ($defaultAddress) {
            $pincodeRecord = DeliveryPincode::where('pincode', $defaultAddress->pincode)->first();
            if ($pincodeRecord && $subtotal < (float) $pincodeRecord->min_order_amount) {
                $deliveryCharge = (float) $pincodeRecord->delivery_charge;
            }
        }

        $totalPayable = $subtotal + $deliveryCharge + $depositAmount;
        $previousDue = (float) $profile->outstanding_financial_balance;
        $allowPayLater = (bool) $profile->allow_pay_later && (($previousDue + $totalPayable) <= (float) $profile->credit_limit);

        return view('checkout.index', compact(
            'cart', 'profile', 'addresses', 'deliveryDates', 'slots', 
            'initialDate', 'subtotal', 'deliveryCharge', 'depositAmount', 
            'exchangeableCount', 'totalPayable', 'previousDue', 'allowPayLater', 'totalLitres'
        ));
    }

    /**
     * AJAX endpoint to retrieve available slots for a selected date
     */
    public function getSlots(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $user = $request->user();
        $branchId = session('active_branch_id', 1);
        $cart = $this->cartService->getOrCreateCart($user, $request->session()->getId(), $branchId);
        $totalLitres = $cart->calculateTotalLitres();

        $slots = $this->slotService->getAvailableSlots($branchId, $request->input('date'), $totalLitres);

        return response()->json([
            'success' => true,
            'slots' => $slots->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'time_range' => $s->formatted_time_range,
                ];
            }),
        ]);
    }

    /**
     * Process checkout submission.
     */
    public function process(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_address_id' => ['required', 'exists:customer_addresses,id'],
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'delivery_slot_id' => ['required', 'exists:delivery_slots,id'],
            'payment_method' => ['required', 'in:cod,pay_later,razorpay'],
            'has_empty_cans_to_exchange' => ['nullable', 'boolean'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $branchId = session('active_branch_id', 1);
        $cart = $this->cartService->getOrCreateCart($user, $request->session()->getId(), $branchId);

        $hasEmptyCans = $request->has('has_empty_cans_to_exchange')
            ? $request->boolean('has_empty_cans_to_exchange')
            : (bool) ($cart->has_empty_cans_to_exchange ?? true);

        try {
            $order = $this->orderService->createOrderFromCart(
                $user,
                $cart,
                (int) $validated['delivery_address_id'],
                $validated['delivery_date'],
                (int) $validated['delivery_slot_id'],
                $validated['payment_method'],
                $validated['customer_notes'] ?? null,
                $validated['idempotency_key'] ?? null,
                $hasEmptyCans
            );

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', "Order #{$order->order_number} confirmed successfully! Delivery is scheduled for {$order->delivery_date->format('d M Y')}.");

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['checkout' => $e->getMessage()]);
        }
    }

    /**
     * Store new customer delivery address from checkout or customer portal.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_person_name' => ['required', 'string', 'max:150'],
            'contact_person_mobile' => ['required', 'string', 'digits:10'],
            'address_type' => ['required', 'in:home,office,other'],
            'house_flat_no' => ['required', 'string', 'max:50'],
            'building_apartment_name' => ['nullable', 'string', 'max:150'],
            'street_address' => ['required', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'area' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'digits:6'],
            'delivery_instructions' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $branchId = session('active_branch_id', 1);
        $profile = $user->customerProfile ?? \App\Models\CustomerProfile::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'customer_type' => 'residential',
            'credit_limit' => 500.00,
            'outstanding_financial_balance' => 0.00,
            'allow_pay_later' => false,
        ]);

        // Check pincode serviceability
        $pincodeRecord = DeliveryPincode::where('pincode', $validated['pincode'])
            ->where('is_serviceable', true)
            ->first();

        if (! $pincodeRecord) {
            $serviceableList = DeliveryPincode::where('is_serviceable', true)->pluck('pincode')->implode(', ');
            return back()->withInput()->withErrors([
                'pincode' => "Pincode {$validated['pincode']} is currently not serviceable. Serviceable areas include: {$serviceableList}",
            ]);
        }

        // If setting as default, uncheck previous defaults
        $isDefault = $request->boolean('is_default') || CustomerAddress::where('customer_profile_id', $profile->id)->count() === 0;
        if ($isDefault) {
            CustomerAddress::where('customer_profile_id', $profile->id)->update(['is_default' => false]);
        }

        CustomerAddress::create([
            'customer_profile_id' => $profile->id,
            'contact_person_name' => $validated['contact_person_name'],
            'contact_person_mobile' => $validated['contact_person_mobile'],
            'address_type' => $validated['address_type'],
            'house_flat_no' => $validated['house_flat_no'],
            'building_apartment_name' => $validated['building_apartment_name'] ?? null,
            'street_address' => $validated['street_address'],
            'landmark' => $validated['landmark'] ?? null,
            'area' => $validated['area'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
            'delivery_instructions' => $validated['delivery_instructions'] ?? null,
            'is_default' => $isDefault,
            'is_active' => true,
        ]);

        return back()->with('success', 'New delivery address added successfully.');
    }
}
