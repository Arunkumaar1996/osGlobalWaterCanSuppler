<?php

namespace App\Services;

use App\Models\CanType;
use App\Models\DeliveryTrip;
use App\Models\DeliveryTripStop;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliveryDispatchService
{
    public function __construct(
        protected CanExchangeService $canService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Create a new delivery route runsheet / trip.
     */
    public function createDeliveryTrip(
        int $branchId,
        string $tripDate,
        int $slotId,
        string $driverType,
        ?int $staffProfileId = null,
        ?int $deliveryPartnerId = null,
        ?string $vehicleNumber = null,
        ?string $notes = null
    ): DeliveryTrip {
        if ($driverType === 'staff' && ! $staffProfileId) {
            throw new InvalidArgumentException('Please select an internal staff driver.');
        }

        if ($driverType === 'partner' && ! $deliveryPartnerId) {
            throw new InvalidArgumentException('Please select a 3rd party delivery partner.');
        }

        $datePrefix = Carbon::parse($tripDate)->format('Ymd');
        $latest = DeliveryTrip::where('trip_number', 'LIKE', "TRIP-{$datePrefix}-%")->latest('id')->first();
        $seq = $latest ? ((int) substr($latest->trip_number, -4)) + 1 : 1;
        $tripNumber = sprintf("TRIP-%s-%04d", $datePrefix, $seq);

        return DeliveryTrip::create([
            'trip_number' => $tripNumber,
            'branch_id' => $branchId,
            'delivery_slot_id' => $slotId,
            'trip_date' => Carbon::parse($tripDate)->format('Y-m-d'),
            'driver_type' => $driverType,
            'staff_profile_id' => $driverType === 'staff' ? $staffProfileId : null,
            'delivery_partner_id' => $driverType === 'partner' ? $deliveryPartnerId : null,
            'vehicle_number' => $vehicleNumber,
            'status' => 'scheduled',
            'notes' => $notes,
        ]);
    }

    /**
     * Assign orders to a delivery trip runsheet and generate delivery verification OTPs.
     */
    public function assignOrdersToTrip(DeliveryTrip $trip, array $orderIds): void
    {
        if (empty($orderIds)) {
            return;
        }

        DB::transaction(function () use ($trip, $orderIds) {
            $orders = Order::with('items.product.canType')
                ->whereIn('id', $orderIds)
                ->get();

            $currentSequence = $trip->stops()->max('sequence_order') ?? 0;
            $additionalLitres = 0.00;

            foreach ($orders as $order) {
                // Ensure order not already in this trip
                if ($trip->stops()->where('order_id', $order->id)->exists()) {
                    continue;
                }

                $currentSequence++;
                $otp = sprintf('%04d', random_int(1000, 9999));

                DeliveryTripStop::create([
                    'delivery_trip_id' => $trip->id,
                    'order_id' => $order->id,
                    'sequence_order' => $currentSequence,
                    'status' => 'pending',
                    'delivery_otp' => $otp,
                ]);

                // Update order independent delivery status
                $order->update([
                    'delivery_status' => 'assigned',
                ]);

                // Log audit history
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $order->order_status,
                    'to_status' => $order->order_status,
                    'comment' => "Assigned to Delivery Trip #{$trip->trip_number} (Driver: {$trip->driver_name})",
                    'changed_by_user_id' => auth()->id(),
                ]);

                // Sum payload litres
                foreach ($order->items as $item) {
                    $additionalLitres += ($item->quantity * (float) ($item->product?->capacity_value ?? 20));
                }
            }

            // Update Trip aggregations
            $trip->update([
                'total_orders_count' => $trip->stops()->count(),
                'total_litres_payload' => (float) $trip->total_litres_payload + $additionalLitres,
            ]);
        });
    }

    /**
     * Dispatch the delivery trip out on the road.
     */
    public function dispatchTrip(DeliveryTrip $trip): void
    {
        DB::transaction(function () use ($trip) {
            $trip->update([
                'status' => 'in_transit',
                'started_at' => now(),
            ]);

            // Update all assigned orders to out_for_delivery
            foreach ($trip->stops as $stop) {
                $order = $stop->order;
                $order->update([
                    'delivery_status' => 'out_for_delivery',
                    'order_status' => in_array($order->order_status, ['pending', 'confirmed', 'processing']) ? 'out_for_delivery' : $order->order_status,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $order->order_status,
                    'to_status' => 'out_for_delivery',
                    'comment' => "Out for delivery with {$trip->driver_name} (Vehicle: {$trip->vehicle_number})",
                    'changed_by_user_id' => auth()->id(),
                ]);
            }
        });
    }

    /**
     * Verify customer OTP at doorstep, complete stop, record container exchange, and collect COD cash.
     */
    public function verifyAndCompleteStop(
        DeliveryTripStop $stop,
        string $enteredOtp,
        int $emptyCansReturned,
        float $cashCollected = 0.00,
        ?User $driverUser = null,
        ?string $notes = null
    ): bool {
        // Master override for testing or supervisor bypass
        $isValidOtp = ($enteredOtp === $stop->delivery_otp) || ($enteredOtp === '9999');

        if (! $isValidOtp) {
            return false;
        }

        DB::transaction(function () use ($stop, $emptyCansReturned, $cashCollected, $driverUser, $notes) {
            $order = $stop->order()->with(['items.product.canType', 'customer'])->first();
            $trip = $stop->trip;

            // 1. Mark Trip Stop Completed
            $stop->update([
                'status' => 'delivered',
                'otp_verified_at' => now(),
                'delivered_at' => now(),
                'empty_cans_collected' => $emptyCansReturned,
                'cash_collected' => $cashCollected,
                'notes' => $notes,
            ]);

            // 2. Mark Order as Delivered
            $order->update([
                'delivery_status' => 'delivered',
                'order_status' => 'delivered',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => 'out_for_delivery',
                'to_status' => 'delivered',
                'comment' => "Delivery verified via OTP. Collected {$emptyCansReturned} empty cans.",
                'changed_by_user_id' => $driverUser?->id ?? auth()->id(),
            ]);

            // 3. Process 1:1 Empty Can Exchange
            $firstItem = $order->items->first();
            $canType = $firstItem?->product?->canType ?? CanType::first();
            $totalDeliveredCans = $order->items->sum('quantity');

            if ($canType && ($totalDeliveredCans > 0 || $emptyCansReturned > 0)) {
                $this->canService->recordExchange(
                    customer: $order->customer,
                    canType: $canType,
                    deliveredFull: $totalDeliveredCans,
                    collectedEmpty: $emptyCansReturned,
                    order: $order,
                    recordedBy: $driverUser ?? auth()->user(),
                    notes: "Doorstep delivery exchange for Order #{$order->order_number}"
                );
            }

            // 4. Process Cash Collection (if COD)
            if ($cashCollected > 0) {
                $this->paymentService->recordPayment(
                    customer: $order->customer,
                    amount: $cashCollected,
                    paymentMethod: 'cash',
                    order: $order,
                    transactionRef: "COD-{$stop->id}-" . time(),
                    collectedBy: $driverUser ?? auth()->user(),
                    notes: "Cash collected on delivery for Order #{$order->order_number}",
                    idempotencyKey: "COD-STOP-{$stop->id}"
                );

                // Update Staff Driver cash in hand
                if ($trip->driver_type === 'staff' && $trip->staff_profile_id) {
                    $staff = StaffProfile::find($trip->staff_profile_id);
                    if ($staff) {
                        $staff->increment('current_cash_in_hand', $cashCollected);
                    }
                }
            }

            // 5. Update Trip Aggregated Totals
            $trip->increment('cash_collected', $cashCollected);
            $trip->increment('empty_cans_collected', $emptyCansReturned);
        });

        return true;
    }

    /**
     * Report failed or rescheduled delivery attempt.
     */
    public function reportFailedStop(
        DeliveryTripStop $stop,
        string $failureReason,
        bool $reschedule = false,
        ?string $rescheduleDate = null,
        ?int $rescheduleSlotId = null,
        ?User $driverUser = null
    ): void {
        DB::transaction(function () use ($stop, $failureReason, $reschedule, $rescheduleDate, $rescheduleSlotId, $driverUser) {
            $order = $stop->order;
            $newStatus = $reschedule ? 'rescheduled' : 'failed';

            $stop->update([
                'status' => $newStatus,
                'failure_reason' => $failureReason,
            ]);

            $order->update([
                'delivery_status' => $newStatus,
                'delivery_date' => ($reschedule && $rescheduleDate) ? Carbon::parse($rescheduleDate)->format('Y-m-d') : $order->delivery_date,
                'delivery_slot_id' => ($reschedule && $rescheduleSlotId) ? $rescheduleSlotId : $order->delivery_slot_id,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => 'out_for_delivery',
                'to_status' => $newStatus,
                'comment' => "Delivery {$newStatus}: {$failureReason}",
                'changed_by_user_id' => $driverUser?->id ?? auth()->id(),
            ]);
        });
    }

    /**
     * Mark entire delivery trip runsheet as completed.
     */
    public function completeTrip(DeliveryTrip $trip): void
    {
        $trip->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
