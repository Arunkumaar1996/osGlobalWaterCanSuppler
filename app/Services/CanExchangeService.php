<?php

namespace App\Services;

use App\Models\CanMovement;
use App\Models\CanType;
use App\Models\CustomerCanBalance;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CanExchangeService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Record a container exchange (1:1 delivery exchange or empty return collection).
     */
    public function recordExchange(
        CustomerProfile $customer,
        CanType $canType,
        int $deliveredFull,
        int $collectedEmpty,
        ?Order $order = null,
        ?User $recordedBy = null,
        ?string $notes = null
    ): CanMovement {
        if ($deliveredFull < 0 || $collectedEmpty < 0) {
            throw new InvalidArgumentException('Delivered and collected can counts cannot be negative.');
        }

        if ($deliveredFull === 0 && $collectedEmpty === 0) {
            throw new InvalidArgumentException('At least one full can must be delivered or empty can collected.');
        }

        return DB::transaction(function () use (
            $customer, $canType, $deliveredFull, $collectedEmpty, $order, $recordedBy, $notes
        ) {
            // Find or initialize balance row and lock for update
            $balance = CustomerCanBalance::firstOrCreate(
                [
                    'customer_profile_id' => $customer->id,
                    'can_type_id' => $canType->id,
                ],
                ['held_cans_count' => 0]
            );

            $balance = CustomerCanBalance::where('id', $balance->id)->lockForUpdate()->first();

            $netChange = $deliveredFull - $collectedEmpty;
            $newBalance = max(0, $balance->held_cans_count + $netChange);
            $balance->update(['held_cans_count' => $newBalance]);

            $movementType = ($deliveredFull > 0) ? 'delivery_exchange' : 'return_collection';
            $defaultNotes = $notes ?: ($movementType === 'delivery_exchange'
                ? "Delivery exchange: {$deliveredFull} full cans delivered, {$collectedEmpty} empty cans returned"
                : "Empty container collection: {$collectedEmpty} empty cans collected");

            return CanMovement::create([
                'customer_profile_id' => $customer->id,
                'branch_id' => $customer->branch_id,
                'can_type_id' => $canType->id,
                'movement_type' => $movementType,
                'cans_delivered_full' => $deliveredFull,
                'cans_collected_empty' => $collectedEmpty,
                'missing_cans' => 0,
                'balance_after_movement' => $newBalance,
                'reference_type' => $order ? Order::class : ($recordedBy ? User::class : null),
                'reference_id' => $order?->id ?? $recordedBy?->id,
                'recorded_by_user_id' => $recordedBy?->id,
                'notes' => $defaultNotes,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Record missing/damaged cans, reduce container holding balance,
     * and automatically bill the penalty fee to the customer's financial ledger.
     */
    public function recordMissingPenalty(
        CustomerProfile $customer,
        CanType $canType,
        int $missingCount,
        User $admin,
        string $reason
    ): array {
        if ($missingCount <= 0) {
            throw new InvalidArgumentException('Missing can count must be at least 1.');
        }

        return DB::transaction(function () use ($customer, $canType, $missingCount, $admin, $reason) {
            $balance = CustomerCanBalance::firstOrCreate(
                [
                    'customer_profile_id' => $customer->id,
                    'can_type_id' => $canType->id,
                ],
                ['held_cans_count' => 0]
            );

            $balance = CustomerCanBalance::where('id', $balance->id)->lockForUpdate()->first();

            $newBalance = max(0, $balance->held_cans_count - $missingCount);
            $balance->update(['held_cans_count' => $newBalance]);

            // 1. Post physical container ledger entry
            $movement = CanMovement::create([
                'customer_profile_id' => $customer->id,
                'branch_id' => $customer->branch_id,
                'can_type_id' => $canType->id,
                'movement_type' => 'missing_penalty',
                'cans_delivered_full' => 0,
                'cans_collected_empty' => 0,
                'missing_cans' => $missingCount,
                'balance_after_movement' => $newBalance,
                'reference_type' => User::class,
                'reference_id' => $admin->id,
                'recorded_by_user_id' => $admin->id,
                'notes' => "Missing/damaged penalty: {$missingCount}x {$canType->name} declared lost/broken. Reason: {$reason}",
                'created_at' => now(),
            ]);

            // 2. Post financial debit adjustment to Customer Ledger (penalty fee)
            $penaltyAmount = round($missingCount * (float) $canType->deposit_amount, 2);
            $financialReason = "Lost/damaged container penalty fee: {$missingCount}x {$canType->name} @ ₹" . number_format($canType->deposit_amount, 2) . " each. {$reason}";

            $ledgerEntry = $this->paymentService->recordAdjustment(
                customer: $customer,
                amount: $penaltyAmount,
                adjustmentType: 'debit',
                admin: $admin,
                reason: $financialReason
            );

            return [
                'movement' => $movement,
                'ledger' => $ledgerEntry,
                'penalty_amount' => $penaltyAmount,
                'new_balance' => $newBalance,
            ];
        });
    }

    /**
     * Reconcile customer physical can count after on-site inventory audit.
     */
    public function reconcileBalance(
        CustomerProfile $customer,
        CanType $canType,
        int $actualPhysicalCount,
        User $admin,
        string $notes
    ): CanMovement {
        if ($actualPhysicalCount < 0) {
            throw new InvalidArgumentException('Actual physical can count cannot be negative.');
        }

        return DB::transaction(function () use ($customer, $canType, $actualPhysicalCount, $admin, $notes) {
            $balance = CustomerCanBalance::firstOrCreate(
                [
                    'customer_profile_id' => $customer->id,
                    'can_type_id' => $canType->id,
                ],
                ['held_cans_count' => 0]
            );

            $balance = CustomerCanBalance::where('id', $balance->id)->lockForUpdate()->first();
            $prevBalance = $balance->held_cans_count;

            $balance->update(['held_cans_count' => $actualPhysicalCount]);

            return CanMovement::create([
                'customer_profile_id' => $customer->id,
                'branch_id' => $customer->branch_id,
                'can_type_id' => $canType->id,
                'movement_type' => 'audit_reconciliation',
                'cans_delivered_full' => 0,
                'cans_collected_empty' => 0,
                'missing_cans' => 0,
                'balance_after_movement' => $actualPhysicalCount,
                'reference_type' => User::class,
                'reference_id' => $admin->id,
                'recorded_by_user_id' => $admin->id,
                'notes' => "Physical audit count reconciliation: adjusted from {$prevBalance} to {$actualPhysicalCount} cans. Notes: {$notes}",
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Get total containers held by a customer across all sizes.
     */
    public function getCustomerTotalHeldCans(CustomerProfile $customer): int
    {
        return (int) CustomerCanBalance::where('customer_profile_id', $customer->id)->sum('held_cans_count');
    }

    /**
     * Get per-can-type breakdown for a customer.
     */
    public function getCustomerCanBalances(CustomerProfile $customer): Collection
    {
        $canTypes = CanType::where('is_active', true)->get();
        $balances = CustomerCanBalance::where('customer_profile_id', $customer->id)->get()->keyBy('can_type_id');

        return $canTypes->map(function ($type) use ($balances) {
            $held = $balances->has($type->id) ? $balances[$type->id]->held_cans_count : 0;
            return (object) [
                'can_type' => $type,
                'held_cans_count' => $held,
            ];
        });
    }
}
