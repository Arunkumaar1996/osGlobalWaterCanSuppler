<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Get or initialize warehouse stock record for a product
     */
    public function getOrCreateStock(int $warehouseId, int $productId): WarehouseStock
    {
        $product = Product::findOrFail($productId);

        return WarehouseStock::firstOrCreate(
            [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
            ],
            [
                'can_type_id' => $product->can_type_id,
                'full_stock' => 0,
                'empty_stock' => 0,
                'under_cleaning' => 0,
                'damaged_stock' => 0,
                'reserved_stock' => 0,
                'reorder_level' => 20,
            ]
        );
    }

    /**
     * Core atomic movement recorder between physical stock states
     */
    public function recordMovement(
        int $warehouseId,
        int $productId,
        string $movementType,
        string $stateFrom,
        string $stateTo,
        int $quantity,
        ?User $user = null,
        $reference = null,
        ?string $notes = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity moved must be greater than zero.');
        }

        $validStates = ['none', 'full', 'empty', 'under_cleaning', 'damaged', 'reserved'];
        if (!in_array($stateFrom, $validStates) || !in_array($stateTo, $validStates)) {
            throw new InvalidArgumentException("Invalid state transition: {$stateFrom} -> {$stateTo}");
        }

        return DB::transaction(function () use (
            $warehouseId, $productId, $movementType, $stateFrom, $stateTo, $quantity, $user, $reference, $notes
        ) {
            $stock = $this->getOrCreateStock($warehouseId, $productId);
            $stock = WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();

            // Deduct from stateFrom if not 'none'
            if ($stateFrom !== 'none') {
                $columnFrom = $stateFrom . '_stock';
                if ($stateFrom === 'under_cleaning') {
                    $columnFrom = 'under_cleaning';
                }

                if ($stock->$columnFrom < $quantity) {
                    throw new InvalidArgumentException(
                        "Insufficient stock in {$stateFrom}. Requested: {$quantity}, Available: {$stock->$columnFrom}"
                    );
                }

                $stock->$columnFrom -= $quantity;
            }

            // Add to stateTo if not 'none'
            if ($stateTo !== 'none') {
                $columnTo = $stateTo . '_stock';
                if ($stateTo === 'under_cleaning') {
                    $columnTo = 'under_cleaning';
                }

                $stock->$columnTo += $quantity;
            }

            $stock->save();

            // Record immutable audit ledger entry
            return InventoryMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'can_type_id' => $stock->can_type_id,
                'user_id' => $user?->id,
                'movement_type' => $movementType,
                'state_from' => $stateFrom,
                'state_to' => $stateTo,
                'quantity' => $quantity,
                'full_stock_after' => $stock->full_stock,
                'empty_stock_after' => $stock->empty_stock,
                'under_cleaning_after' => $stock->under_cleaning,
                'damaged_stock_after' => $stock->damaged_stock,
                'reserved_stock_after' => $stock->reserved_stock,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'notes' => $notes,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Receive goods from purchase order into full warehouse stock
     */
    public function receivePurchasedStock(
        int $warehouseId,
        int $productId,
        int $acceptedQuantity,
        ?User $user = null,
        $reference = null,
        ?string $notes = null
    ): ?InventoryMovement {
        if ($acceptedQuantity <= 0) {
            return null;
        }

        return $this->recordMovement(
            warehouseId: $warehouseId,
            productId: $productId,
            movementType: 'purchase_received',
            stateFrom: 'none',
            stateTo: 'full',
            quantity: $acceptedQuantity,
            user: $user,
            reference: $reference,
            notes: $notes ?: 'Stock received from supplier purchase order'
        );
    }

    /**
     * Move returned empty cans into the cleaning/sanitizing bay
     */
    public function sendEmptyToCleaning(
        int $warehouseId,
        int $productId,
        int $quantity,
        ?User $user = null,
        ?string $notes = null
    ): InventoryMovement {
        return $this->recordMovement(
            warehouseId: $warehouseId,
            productId: $productId,
            movementType: 'sent_to_cleaning',
            stateFrom: 'empty',
            stateTo: 'under_cleaning',
            quantity: $quantity,
            user: $user,
            notes: $notes ?: 'Batch routed to washing and UV sanitizing cycle'
        );
    }

    /**
     * Complete refill and seal: move sanitized cans to full stock ready for delivery
     */
    public function completeRefillToFull(
        int $warehouseId,
        int $productId,
        int $quantity,
        ?User $user = null,
        ?string $notes = null
    ): InventoryMovement {
        return $this->recordMovement(
            warehouseId: $warehouseId,
            productId: $productId,
            movementType: 'refilled_to_full',
            stateFrom: 'under_cleaning',
            stateTo: 'full',
            quantity: $quantity,
            user: $user,
            notes: $notes ?: 'Refilling completed; hermetically sealed with batch QC pass'
        );
    }

    /**
     * Quarantine a damaged or cracked can
     */
    public function markAsDamaged(
        int $warehouseId,
        int $productId,
        string $sourceState,
        int $quantity,
        ?User $user = null,
        ?string $reason = null
    ): InventoryMovement {
        return $this->recordMovement(
            warehouseId: $warehouseId,
            productId: $productId,
            movementType: 'damaged_writeoff',
            stateFrom: $sourceState,
            stateTo: 'damaged',
            quantity: $quantity,
            user: $user,
            notes: $reason ?: 'Damaged / leaking container quarantined'
        );
    }

    /**
     * Reconcile physical warehouse stock audit counts
     */
    public function reconcileStockAudit(
        int $warehouseId,
        int $productId,
        array $actualCounts,
        User $admin,
        string $reason
    ): array {
        return DB::transaction(function () use ($warehouseId, $productId, $actualCounts, $admin, $reason) {
            $stock = $this->getOrCreateStock($warehouseId, $productId);
            $stock = WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();

            $movements = [];
            $states = ['full', 'empty', 'under_cleaning', 'damaged'];

            foreach ($states as $state) {
                if (isset($actualCounts[$state])) {
                    $column = $state . '_stock';
                    if ($state === 'under_cleaning') {
                        $column = 'under_cleaning';
                    }

                    $current = (int) $stock->$column;
                    $actual = max(0, (int) $actualCounts[$state]);
                    $diff = $actual - $current;

                    if ($diff !== 0) {
                        $stock->$column = $actual;

                        $movements[] = InventoryMovement::create([
                            'warehouse_id' => $warehouseId,
                            'product_id' => $productId,
                            'can_type_id' => $stock->can_type_id,
                            'user_id' => $admin->id,
                            'movement_type' => 'stock_audit_adjustment',
                            'state_from' => $diff < 0 ? $state : 'none',
                            'state_to' => $diff > 0 ? $state : 'none',
                            'quantity' => abs($diff),
                            'full_stock_after' => $stock->full_stock,
                            'empty_stock_after' => $stock->empty_stock,
                            'under_cleaning_after' => $stock->under_cleaning,
                            'damaged_stock_after' => $stock->damaged_stock,
                            'reserved_stock_after' => $stock->reserved_stock,
                            'notes' => "Physical audit adjustment for {$state}: {$diff} cans ({$reason})",
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            if (isset($actualCounts['reorder_level'])) {
                $stock->reorder_level = max(0, (int) $actualCounts['reorder_level']);
            }

            $stock->save();

            return $movements;
        });
    }

    /**
     * Initiate an inter-warehouse stock transfer
     */
    public function initiateTransfer(
        int $sourceWhId,
        int $destWhId,
        array $items,
        User $user,
        ?string $notes = null
    ): StockTransfer {
        if ($sourceWhId === $destWhId) {
            throw new InvalidArgumentException('Source and destination warehouse cannot be the same.');
        }

        return DB::transaction(function () use ($sourceWhId, $destWhId, $items, $user, $notes) {
            $year = date('Y');
            $latest = StockTransfer::where('transfer_number', 'LIKE', "TRF-{$year}-%")->latest('id')->first();
            $seq = $latest ? ((int) substr($latest->transfer_number, -4)) + 1 : 1;
            $trfNumber = sprintf("TRF-%s-%04d", $year, $seq);

            $transfer = StockTransfer::create([
                'transfer_number' => $trfNumber,
                'source_warehouse_id' => $sourceWhId,
                'destination_warehouse_id' => $destWhId,
                'status' => 'in_transit',
                'initiated_by_user_id' => $user->id,
                'initiated_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($items as $itemData) {
                $qty = (int) $itemData['quantity'];
                $stockType = $itemData['stock_type'] ?? 'full';
                $productId = (int) $itemData['product_id'];

                $stock = $this->getOrCreateStock($sourceWhId, $productId);

                // Deduct from source warehouse
                $this->recordMovement(
                    warehouseId: $sourceWhId,
                    productId: $productId,
                    movementType: 'transfer_out',
                    stateFrom: $stockType,
                    stateTo: 'none',
                    quantity: $qty,
                    user: $user,
                    reference: $transfer,
                    notes: "Dispatched to transfer {$trfNumber}"
                );

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'can_type_id' => $stock->can_type_id,
                    'stock_type' => $stockType,
                    'quantity_sent' => $qty,
                    'quantity_received' => 0,
                    'quantity_damaged_in_transit' => 0,
                ]);
            }

            return $transfer->load(['items.product', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }

    /**
     * Complete an incoming inter-warehouse transfer at destination warehouse
     */
    public function completeTransfer(
        StockTransfer $transfer,
        array $receivedItems,
        User $user,
        ?string $notes = null
    ): StockTransfer {
        if ($transfer->status === 'completed') {
            throw new InvalidArgumentException('Stock transfer is already completed.');
        }

        return DB::transaction(function () use ($transfer, $receivedItems, $user, $notes) {
            $destWhId = $transfer->destination_warehouse_id;

            foreach ($receivedItems as $itemData) {
                $trfItem = StockTransferItem::where('id', $itemData['stock_transfer_item_id'])
                    ->where('stock_transfer_id', $transfer->id)
                    ->firstOrFail();

                $received = max(0, (int) $itemData['quantity_received']);
                $damaged = max(0, (int) $itemData['quantity_damaged_in_transit']);

                $trfItem->update([
                    'quantity_received' => $received,
                    'quantity_damaged_in_transit' => $damaged,
                ]);

                // Credit accepted received cans to destination warehouse
                if ($received > 0) {
                    $this->recordMovement(
                        warehouseId: $destWhId,
                        productId: $trfItem->product_id,
                        movementType: 'transfer_in',
                        stateFrom: 'none',
                        stateTo: $trfItem->stock_type,
                        quantity: $received,
                        user: $user,
                        reference: $transfer,
                        notes: "Received from transfer {$transfer->transfer_number}"
                    );
                }

                // If damaged in transit, credit to damaged_stock at destination warehouse for salvage/claim
                if ($damaged > 0) {
                    $this->recordMovement(
                        warehouseId: $destWhId,
                        productId: $trfItem->product_id,
                        movementType: 'damaged_writeoff',
                        stateFrom: 'none',
                        stateTo: 'damaged',
                        quantity: $damaged,
                        user: $user,
                        reference: $transfer,
                        notes: "Damaged in transit during {$transfer->transfer_number}"
                    );
                }
            }

            $transfer->update([
                'status' => 'completed',
                'received_by_user_id' => $user->id,
                'completed_at' => now(),
                'notes' => $notes ?: $transfer->notes,
            ]);

            return $transfer->load(['items.product', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }
}
