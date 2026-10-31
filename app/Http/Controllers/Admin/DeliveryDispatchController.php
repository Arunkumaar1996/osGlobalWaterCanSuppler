<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\DeliverySlot;
use App\Models\DeliverySlotCapacity;
use App\Models\DeliveryTrip;
use App\Models\DeliveryTripStop;
use App\Models\Order;
use App\Models\StaffProfile;
use App\Services\DeliveryDispatchService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryDispatchController extends Controller
{
    public function __construct(
        protected DeliveryDispatchService $dispatchService
    ) {}

    /**
     * Delivery Fleet Dashboard & Runsheets Overview.
     */
    public function index(Request $request): View
    {
        $branchId = session('active_branch_id', 1);
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));

        // Query Trips for selected date
        $tripsQuery = DeliveryTrip::with(['deliverySlot', 'staffProfile.user', 'deliveryPartner', 'stops'])
            ->where('branch_id', $branchId)
            ->whereDate('trip_date', $selectedDate)
            ->latest('id');

        if ($request->filled('status')) {
            $tripsQuery->where('status', $request->input('status'));
        }

        $trips = $tripsQuery->get();

        // Unassigned Orders waiting for dispatch on this date
        $unassignedOrders = Order::with(['deliverySlot', 'customer.user', 'items.product'])
            ->where('branch_id', $branchId)
            ->whereDate('delivery_date', $selectedDate)
            ->whereIn('delivery_status', ['pending', 'rescheduled'])
            ->whereNotIn('order_status', ['cancelled', 'delivered'])
            ->get();

        $slots = DeliverySlot::where('branch_id', $branchId)->where('is_active', true)->get();

        return view('admin.delivery.index', compact(
            'trips',
            'unassignedOrders',
            'slots',
            'selectedDate'
        ));
    }

    /**
     * Show form to create a new delivery trip runsheet.
     */
    public function createTrip(Request $request): View
    {
        $branchId = session('active_branch_id', 1);
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $selectedSlotId = $request->input('slot_id');

        $slots = DeliverySlot::where('branch_id', $branchId)->where('is_active', true)->get();
        $staffDrivers = StaffProfile::with('user')
            ->where('branch_id', $branchId)
            ->where('is_available_for_delivery', true)
            ->get();
        $partners = DeliveryPartner::where('branch_id', $branchId)->where('is_active', true)->get();

        // Pending unassigned orders for this date and slot
        $pendingOrdersQuery = Order::with(['customer.user', 'items.product'])
            ->where('branch_id', $branchId)
            ->whereDate('delivery_date', $selectedDate)
            ->whereIn('delivery_status', ['pending', 'rescheduled'])
            ->whereNotIn('order_status', ['cancelled', 'delivered']);

        if ($selectedSlotId) {
            $pendingOrdersQuery->where('delivery_slot_id', $selectedSlotId);
        }

        $pendingOrders = $pendingOrdersQuery->get();

        return view('admin.delivery.trip-create', compact(
            'slots',
            'staffDrivers',
            'partners',
            'pendingOrders',
            'selectedDate',
            'selectedSlotId'
        ));
    }

    /**
     * Store new delivery trip and assign orders.
     */
    public function storeTrip(Request $request): RedirectResponse
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'trip_date' => ['required', 'date'],
            'delivery_slot_id' => ['required', 'exists:delivery_slots,id'],
            'driver_type' => ['required', 'in:staff,partner'],
            'staff_profile_id' => ['nullable', 'required_if:driver_type,staff', 'exists:staff_profiles,id'],
            'delivery_partner_id' => ['nullable', 'required_if:driver_type,partner', 'exists:delivery_partners,id'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
            'order_ids' => ['nullable', 'array'],
            'order_ids.*' => ['exists:orders,id'],
        ]);

        $trip = $this->dispatchService->createDeliveryTrip(
            branchId: $branchId,
            tripDate: $validated['trip_date'],
            slotId: (int) $validated['delivery_slot_id'],
            driverType: $validated['driver_type'],
            staffProfileId: ! empty($validated['staff_profile_id']) ? (int) $validated['staff_profile_id'] : null,
            deliveryPartnerId: ! empty($validated['delivery_partner_id']) ? (int) $validated['delivery_partner_id'] : null,
            vehicleNumber: $validated['vehicle_number'] ?? null,
            notes: $validated['notes'] ?? null
        );

        if (! empty($validated['order_ids'])) {
            $this->dispatchService->assignOrdersToTrip($trip, $validated['order_ids']);
        }

        return redirect()->route('admin.delivery.trips.show', $trip->id)
            ->with('success', "Delivery Trip runsheet #{$trip->trip_number} created with {$trip->stops()->count()} assigned stops.");
    }

    /**
     * Show delivery trip details, route runsheet, and stop verification.
     */
    public function showTrip(DeliveryTrip $trip): View
    {
        $trip->load([
            'deliverySlot',
            'staffProfile.user',
            'deliveryPartner',
            'stops.order.customer.user',
            'stops.order.items.product'
        ]);

        return view('admin.delivery.trip-show', compact('trip'));
    }

    /**
     * Dispatch trip out for delivery.
     */
    public function dispatchTrip(DeliveryTrip $trip): RedirectResponse
    {
        if ($trip->stops->isEmpty()) {
            return back()->withErrors(['dispatch' => 'Cannot dispatch an empty trip. Please assign at least one order.']);
        }

        $this->dispatchService->dispatchTrip($trip);

        return back()->with('success', "Trip #{$trip->trip_number} is now In Transit. Customer orders updated to Out for Delivery.");
    }

    /**
     * Complete entire trip.
     */
    public function completeTrip(DeliveryTrip $trip): RedirectResponse
    {
        $this->dispatchService->completeTrip($trip);

        return back()->with('success', "Trip #{$trip->trip_number} marked as Completed.");
    }

    /**
     * Verify OTP and complete stop delivery.
     */
    public function completeStop(Request $request, DeliveryTripStop $stop): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'string'],
            'empty_cans_collected' => ['required', 'integer', 'min:0'],
            'cash_collected' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $success = $this->dispatchService->verifyAndCompleteStop(
            stop: $stop,
            enteredOtp: $validated['otp'],
            emptyCansReturned: (int) $validated['empty_cans_collected'],
            cashCollected: (float) ($validated['cash_collected'] ?? 0.00),
            driverUser: $request->user(),
            notes: $validated['notes'] ?? null
        );

        if (! $success) {
            return back()->withErrors(['otp' => 'Invalid delivery OTP entered. Please check with customer or enter master override.']);
        }

        return back()->with('success', "Order #{$stop->order->order_number} successfully verified and marked as delivered.");
    }

    /**
     * Report failed stop delivery.
     */
    public function failStop(Request $request, DeliveryTripStop $stop): RedirectResponse
    {
        $validated = $request->validate([
            'failure_reason' => ['required', 'string', 'min:3', 'max:255'],
            'reschedule' => ['nullable', 'boolean'],
            'reschedule_date' => ['nullable', 'date', 'after_or_equal:today'],
            'reschedule_slot_id' => ['nullable', 'exists:delivery_slots,id'],
        ]);

        $this->dispatchService->reportFailedStop(
            stop: $stop,
            failureReason: $validated['failure_reason'],
            reschedule: (bool) ($validated['reschedule'] ?? false),
            rescheduleDate: $validated['reschedule_date'] ?? null,
            rescheduleSlotId: ! empty($validated['reschedule_slot_id']) ? (int) $validated['reschedule_slot_id'] : null,
            driverUser: $request->user()
        );

        return back()->with('success', "Delivery stop marked as " . ($validated['reschedule'] ?? false ? 'rescheduled' : 'failed') . ".");
    }

    /**
     * Delivery Partners directory.
     */
    public function partnersIndex(Request $request): View
    {
        $branchId = session('active_branch_id', 1);
        $partners = DeliveryPartner::where('branch_id', $branchId)->latest('id')->paginate(15);

        return view('admin.delivery.partners', compact('partners'));
    }

    /**
     * Store new Delivery Partner.
     */
    public function partnerStore(Request $request): RedirectResponse
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['required', 'string', 'max:150'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email'],
            'billing_type' => ['required', 'in:per_order,per_can,per_km,monthly_fixed'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DeliveryPartner::create(array_merge($validated, ['branch_id' => $branchId]));

        return back()->with('success', "Delivery partner {$validated['name']} registered successfully.");
    }

    /**
     * Delivery Slots & Capacity Limits management.
     */
    public function slotsIndex(Request $request): View
    {
        $branchId = session('active_branch_id', 1);
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $slots = DeliverySlot::where('branch_id', $branchId)->get();
        $capacities = DeliverySlotCapacity::whereIn('delivery_slot_id', $slots->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('delivery_slot_id');

        return view('admin.delivery.slots', compact('slots', 'capacities', 'date'));
    }

    /**
     * Block or unblock a delivery slot for a given date.
     */
    public function slotToggleBlock(Request $request, DeliverySlot $slot): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $capacity = DeliverySlotCapacity::firstOrCreate(
            ['delivery_slot_id' => $slot->id, 'date' => $validated['date']],
            ['reserved_orders_count' => 0, 'reserved_litres_volume' => 0, 'is_blocked' => false]
        );

        $capacity->update(['is_blocked' => ! $capacity->is_blocked]);

        $statusLabel = $capacity->is_blocked ? 'blocked' : 'unblocked';

        return back()->with('success', "Slot {$slot->name} for {$validated['date']} is now {$statusLabel}.");
    }
}
