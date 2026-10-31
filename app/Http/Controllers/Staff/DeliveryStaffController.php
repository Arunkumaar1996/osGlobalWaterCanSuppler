<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTrip;
use App\Models\DeliveryTripStop;
use App\Models\Payment;
use App\Services\DeliveryDispatchService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryStaffController extends Controller
{
    public function __construct(
        protected DeliveryDispatchService $dispatchService
    ) {}

    /**
     * Staff Mobile Delivery Runsheet View.
     */
    public function deliveries(Request $request): View
    {
        $staff = $request->user()->staffProfile;

        if (! $staff) {
            abort(403, 'No staff profile associated with this account.');
        }

        // Active or today's scheduled runsheet trip
        $today = Carbon::today()->format('Y-m-d');
        $trip = DeliveryTrip::with([
            'deliverySlot',
            'stops.order.customer.user',
            'stops.order.items.product'
        ])
        ->where('staff_profile_id', $staff->id)
        ->whereDate('trip_date', $today)
        ->first();

        // Fallback to most recent pending/in_transit trip if none today
        if (! $trip) {
            $trip = DeliveryTrip::with([
                'deliverySlot',
                'stops.order.customer.user',
                'stops.order.items.product'
            ])
            ->where('staff_profile_id', $staff->id)
            ->whereIn('status', ['scheduled', 'in_transit'])
            ->latest('id')
            ->first();
        }

        $stops = $trip ? $trip->stops : collect();
        $deliveredCount = $stops->where('status', 'delivered')->count();
        $pendingCount = $stops->whereIn('status', ['pending', 'arrived'])->count();
        $failedCount = $stops->whereIn('status', ['failed', 'rescheduled'])->count();

        return view('staff.deliveries', compact(
            'staff',
            'trip',
            'stops',
            'deliveredCount',
            'pendingCount',
            'failedCount'
        ));
    }

    /**
     * Doorstep OTP verification & handover completion.
     */
    public function verifyStop(Request $request, DeliveryTripStop $stop): RedirectResponse
    {
        $staff = $request->user()->staffProfile;

        if ($stop->trip->staff_profile_id !== $staff->id && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized trip stop access.');
        }

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
            return back()->withErrors(['otp' => 'Invalid OTP! Please request the correct 4-digit PIN from the customer.']);
        }

        return back()->with('success', "Order #{$stop->order->order_number} verified and marked as delivered!");
    }

    /**
     * Report failed or rescheduled delivery attempt.
     */
    public function failStop(Request $request, DeliveryTripStop $stop): RedirectResponse
    {
        $staff = $request->user()->staffProfile;

        if ($stop->trip->staff_profile_id !== $staff->id && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized trip stop access.');
        }

        $validated = $request->validate([
            'failure_reason' => ['required', 'string', 'min:3', 'max:255'],
            'reschedule' => ['nullable', 'boolean'],
        ]);

        $this->dispatchService->reportFailedStop(
            stop: $stop,
            failureReason: $validated['failure_reason'],
            reschedule: (bool) ($validated['reschedule'] ?? false),
            driverUser: $request->user()
        );

        return back()->with('success', "Delivery stop marked as " . ($validated['reschedule'] ?? false ? 'rescheduled' : 'failed') . ".");
    }

    /**
     * Staff Cash Collection & Handover Passbook.
     */
    public function cashLog(Request $request): View
    {
        $staff = $request->user()->staffProfile;

        $payments = Payment::with('order')
            ->where('collected_by_user_id', $request->user()->id)
            ->where('payment_method', 'cash')
            ->latest('id')
            ->paginate(20);

        return view('staff.cash-log', compact('staff', 'payments'));
    }
}
