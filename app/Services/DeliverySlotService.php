<?php

namespace App\Services;

use App\Models\DeliverySlot;
use App\Models\DeliverySlotCapacity;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliverySlotService
{
    /**
     * Retrieve available slots for a given branch, delivery date, and requested water litre volume
     */
    public function getAvailableSlots(int $branchId, string $date, float $orderLitres = 0): Collection
    {
        $deliveryDate = Carbon::parse($date)->startOfDay();
        $isToday = $deliveryDate->isToday();
        $now = Carbon::now();

        $slots = DeliverySlot::where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('start_time', 'asc')
            ->get();

        $capacities = DeliverySlotCapacity::whereIn('delivery_slot_id', $slots->pluck('id'))
            ->where('date', $deliveryDate->format('Y-m-d'))
            ->get()
            ->keyBy('delivery_slot_id');

        return $slots->filter(function ($slot) use ($capacities, $deliveryDate, $isToday, $now, $orderLitres) {
            // 1. Check cutoff lead minutes if scheduling for today
            if ($isToday) {
                $slotStartTime = Carbon::parse($deliveryDate->format('Y-m-d') . ' ' . $slot->start_time);
                $cutoffDeadline = $slotStartTime->copy()->subMinutes($slot->cutoff_lead_minutes);

                if ($now->greaterThanOrEqualTo($cutoffDeadline)) {
                    return false; // Booking cutoff has passed for this slot
                }
            }

            // 2. Check existing bookings & capacity caps
            $capacity = $capacities->get($slot->id);
            if ($capacity) {
                if ($capacity->is_blocked) {
                    return false;
                }

                if ($capacity->reserved_orders_count >= $slot->max_orders_capacity) {
                    return false; // Order count reached limit
                }

                if (($capacity->reserved_litres_volume + $orderLitres) > $slot->max_litres_capacity) {
                    return false; // Vehicle volume limit exceeded
                }
            }

            return true;
        })->values();
    }

    /**
     * Atomically reserve delivery slot capacity with row locking
     */
    public function reserveCapacity(int $slotId, string $date, float $litres): DeliverySlotCapacity
    {
        return DB::transaction(function () use ($slotId, $date, $litres) {
            $slot = DeliverySlot::where('id', $slotId)->firstOrFail();
            $formattedDate = Carbon::parse($date)->format('Y-m-d');

            // Obtain row lock or create record
            $capacity = DeliverySlotCapacity::where('delivery_slot_id', $slotId)
                ->where('date', $formattedDate)
                ->lockForUpdate()
                ->first();

            if (! $capacity) {
                $capacity = DeliverySlotCapacity::create([
                    'delivery_slot_id' => $slotId,
                    'date' => $formattedDate,
                    'reserved_orders_count' => 0,
                    'reserved_litres_volume' => 0,
                    'is_blocked' => false,
                ]);
            }

            if ($capacity->is_blocked) {
                throw new InvalidArgumentException('Selected delivery slot is currently blocked for bookings.');
            }

            if ($capacity->reserved_orders_count >= $slot->max_orders_capacity) {
                throw new InvalidArgumentException('Selected delivery slot has reached maximum order capacity.');
            }

            if (($capacity->reserved_litres_volume + $litres) > $slot->max_litres_capacity) {
                throw new InvalidArgumentException('Selected delivery slot cannot accommodate the requested water volume.');
            }

            $capacity->increment('reserved_orders_count', 1);
            $capacity->increment('reserved_litres_volume', (int) $litres);

            return $capacity;
        });
    }

    /**
     * Release delivery slot capacity (e.g. on order cancellation)
     */
    public function releaseCapacity(int $slotId, string $date, float $litres): void
    {
        DB::transaction(function () use ($slotId, $date, $litres) {
            $formattedDate = Carbon::parse($date)->format('Y-m-d');

            $capacity = DeliverySlotCapacity::where('delivery_slot_id', $slotId)
                ->where('date', $formattedDate)
                ->lockForUpdate()
                ->first();

            if ($capacity) {
                $capacity->decrement('reserved_orders_count', 1);
                $capacity->decrement('reserved_litres_volume', min($capacity->reserved_litres_volume, (int) $litres));
            }
        });
    }
}
