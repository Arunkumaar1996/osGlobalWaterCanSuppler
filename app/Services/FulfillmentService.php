<?php

namespace App\Services;

use App\Models\DispatchManifest;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderFulfillmentItem;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FulfillmentService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Allocate items of an order to a fulfillment source (Warehouse inventory or Supplier)
     */
    public function allocateFulfillment(
        Order $order,
        string $sourceType,
        int $sourceId,
        array $allocations,
        User $user,
        ?string $notes = null
    ): OrderFulfillment {
        if (!in_array($sourceType, ['warehouse', 'supplier'])) {
            throw new InvalidArgumentException("Invalid fulfillment source type: {$sourceType}");
        }

        if (in_array($order->order_status, ['cancelled', 'completed'])) {
            throw new InvalidArgumentException("Cannot allocate fulfillment for order with status: {$order->order_status}");
        }

        return DB::transaction(function () use ($order, $sourceType, $sourceId, $allocations, $user, $notes) {
            $warehouseId = $sourceType === 'warehouse' ? $sourceId : null;
            $supplierId = $sourceType === 'supplier' ? $sourceId : null;

            if ($warehouseId) {
                Warehouse::findOrFail($warehouseId);
            }
            if ($supplierId) {
                Supplier::findOrFail($supplierId);
            }

            // Generate sequential fulfillment number
            $year = date('Y');
            $latest = OrderFulfillment::where('fulfillment_number', 'LIKE', "FUL-{$year}-%")->latest('id')->first();
            $seq = $latest ? ((int) substr($latest->fulfillment_number, -4)) + 1 : 1;
            $fulNumber = sprintf("FUL-%s-%04d", $year, $seq);

            $fulfillment = OrderFulfillment::create([
                'fulfillment_number' => $fulNumber,
                'order_id' => $order->id,
                'source_type' => $sourceType,
                'warehouse_id' => $warehouseId,
                'supplier_id' => $supplierId,
                'status' => 'allocated',
                'assigned_by_user_id' => $user->id,
                'allocated_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($allocations as $alloc) {
                $orderItemId = (int) $alloc['order_item_id'];
                $qty = (int) $alloc['quantity'];

                if ($qty <= 0) {
                    continue;
                }

                $orderItem = OrderItem::where('id', $orderItemId)
                    ->where('order_id', $order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($orderItem->unfulfilled_quantity < $qty) {
                    throw new InvalidArgumentException(
                        "Cannot allocate {$qty} for item {$orderItem->product_name_snapshot}. Only {$orderItem->unfulfilled_quantity} unfulfilled."
                    );
                }

                // If fulfilling from warehouse, verify and hold stock reservation
                if ($warehouseId) {
                    $stock = $this->inventoryService->getOrCreateStock($warehouseId, $orderItem->product_id);
                    $stock = WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();

                    if ($stock->available_stock < $qty) {
                        throw new InvalidArgumentException(
                            "Insufficient available warehouse stock for '{$orderItem->product_name_snapshot}'. Requested: {$qty}, Available: {$stock->available_stock}"
                        );
                    }

                    // Increment reserved stock
                    $stock->reserved_stock += $qty;
                    $stock->save();

                    // Log audit movement for reservation hold
                    InventoryMovement::create([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $orderItem->product_id,
                        'can_type_id' => $stock->can_type_id,
                        'user_id' => $user->id,
                        'movement_type' => 'reservation_held',
                        'state_from' => 'full',
                        'state_to' => 'reserved',
                        'quantity' => $qty,
                        'full_stock_after' => $stock->full_stock,
                        'empty_stock_after' => $stock->empty_stock,
                        'under_cleaning_after' => $stock->under_cleaning,
                        'damaged_stock_after' => $stock->damaged_stock,
                        'reserved_stock_after' => $stock->reserved_stock,
                        'reference_type' => OrderFulfillment::class,
                        'reference_id' => $fulfillment->id,
                        'notes' => "Stock reserved for Order {$order->order_number} ({$fulNumber})",
                        'created_at' => now(),
                    ]);
                }

                // Record fulfillment line item
                OrderFulfillmentItem::create([
                    'order_fulfillment_id' => $fulfillment->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity' => $qty,
                ]);

                // Update fulfilled quantity counter
                $orderItem->fulfilled_quantity += $qty;
                $orderItem->save();
            }

            // Re-evaluate Order Fulfillment Status
            $this->syncOrderFulfillmentStatus($order, $user);

            return $fulfillment->load(['items.product', 'warehouse', 'supplier', 'order']);
        });
    }

    /**
     * Cancel an active fulfillment and release held reservations
     */
    public function cancelFulfillment(OrderFulfillment $fulfillment, User $user, string $reason): void
    {
        if (in_array($fulfillment->status, ['completed', 'cancelled'])) {
            throw new InvalidArgumentException("Fulfillment is already {$fulfillment->status}.");
        }

        DB::transaction(function () use ($fulfillment, $user, $reason) {
            $order = $fulfillment->order()->lockForUpdate()->first();

            // Release reservations & decrease fulfilled quantities
            foreach ($fulfillment->items as $item) {
                $orderItem = OrderItem::where('id', $item->order_item_id)->lockForUpdate()->first();
                if ($orderItem) {
                    $orderItem->fulfilled_quantity = max(0, $orderItem->fulfilled_quantity - $item->quantity);
                    $orderItem->save();
                }

                // If warehouse fulfillment, release reserved stock
                if ($fulfillment->source_type === 'warehouse' && $fulfillment->warehouse_id) {
                    $stock = WarehouseStock::where('warehouse_id', $fulfillment->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        $stock->reserved_stock = max(0, $stock->reserved_stock - $item->quantity);
                        $stock->save();

                        InventoryMovement::create([
                            'warehouse_id' => $fulfillment->warehouse_id,
                            'product_id' => $item->product_id,
                            'can_type_id' => $stock->can_type_id,
                            'user_id' => $user->id,
                            'movement_type' => 'reservation_released',
                            'state_from' => 'reserved',
                            'state_to' => 'full',
                            'quantity' => $item->quantity,
                            'full_stock_after' => $stock->full_stock,
                            'empty_stock_after' => $stock->empty_stock,
                            'under_cleaning_after' => $stock->under_cleaning,
                            'damaged_stock_after' => $stock->damaged_stock,
                            'reserved_stock_after' => $stock->reserved_stock,
                            'reference_type' => OrderFulfillment::class,
                            'reference_id' => $fulfillment->id,
                            'notes' => "Reservation released upon cancellation of {$fulfillment->fulfillment_number} ({$reason})",
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            $fulfillment->update([
                'status' => 'cancelled',
                'notes' => ($fulfillment->notes ? $fulfillment->notes . "\n" : '') . "Cancelled: {$reason}",
            ]);

            $this->syncOrderFulfillmentStatus($order, $user);
        });
    }

    /**
     * Dispatch a fulfillment (physical cans leave facility or supplier sends consignment)
     */
    public function dispatchFulfillment(OrderFulfillment $fulfillment, User $user): void
    {
        if ($fulfillment->status !== 'allocated' && $fulfillment->status !== 'ready_for_dispatch') {
            throw new InvalidArgumentException("Only allocated fulfillments can be dispatched.");
        }

        DB::transaction(function () use ($fulfillment, $user) {
            $order = $fulfillment->order()->lockForUpdate()->first();

            // If warehouse sourcing, decrement physical full_stock and reserved_stock
            if ($fulfillment->source_type === 'warehouse' && $fulfillment->warehouse_id) {
                foreach ($fulfillment->items as $item) {
                    $stock = WarehouseStock::where('warehouse_id', $fulfillment->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        $stock->full_stock = max(0, $stock->full_stock - $item->quantity);
                        $stock->reserved_stock = max(0, $stock->reserved_stock - $item->quantity);
                        $stock->save();

                        InventoryMovement::create([
                            'warehouse_id' => $fulfillment->warehouse_id,
                            'product_id' => $item->product_id,
                            'can_type_id' => $stock->can_type_id,
                            'user_id' => $user->id,
                            'movement_type' => 'order_dispatched',
                            'state_from' => 'full',
                            'state_to' => 'none',
                            'quantity' => $item->quantity,
                            'full_stock_after' => $stock->full_stock,
                            'empty_stock_after' => $stock->empty_stock,
                            'under_cleaning_after' => $stock->under_cleaning,
                            'damaged_stock_after' => $stock->damaged_stock,
                            'reserved_stock_after' => $stock->reserved_stock,
                            'reference_type' => OrderFulfillment::class,
                            'reference_id' => $fulfillment->id,
                            'notes' => "Dispatched for Order {$order->order_number} ({$fulfillment->fulfillment_number})",
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            $fulfillment->update([
                'status' => 'dispatched',
                'dispatched_at' => now(),
            ]);

            // Advance order delivery status
            if ($order->delivery_status === 'pending') {
                $order->update(['delivery_status' => 'out_for_delivery']);
            }
        });
    }

    /**
     * Generate batch dispatch manifest / packing list
     */
    public function generateManifest(
        int $branchId,
        ?int $warehouseId,
        array $fulfillmentIds,
        ?string $vehicle,
        ?string $driverName,
        ?string $driverPhone,
        User $user,
        ?string $notes = null
    ): DispatchManifest {
        return DB::transaction(function () use (
            $branchId, $warehouseId, $fulfillmentIds, $vehicle, $driverName, $driverPhone, $user, $notes
        ) {
            $year = date('Y');
            $latest = DispatchManifest::where('manifest_number', 'LIKE', "MAN-{$year}-%")->latest('id')->first();
            $seq = $latest ? ((int) substr($latest->manifest_number, -4)) + 1 : 1;
            $manNumber = sprintf("MAN-%s-%04d", $year, $seq);

            $manifest = DispatchManifest::create([
                'manifest_number' => $manNumber,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'status' => 'generated',
                'vehicle_number' => $vehicle,
                'driver_name' => $driverName,
                'driver_phone' => $driverPhone,
                'generated_by_user_id' => $user->id,
                'generated_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($fulfillmentIds as $fulId) {
                $fulfillment = OrderFulfillment::findOrFail($fulId);
                $manifest->fulfillments()->attach($fulfillment->id);

                if ($fulfillment->status === 'allocated') {
                    $fulfillment->update(['status' => 'ready_for_dispatch']);
                }
            }

            return $manifest->load(['fulfillments.order.customer', 'fulfillments.items.product', 'warehouse']);
        });
    }

    /**
     * Cancel an order completely and release all associated fulfillments and reservations
     */
    public function cancelOrder(Order $order, User $user, string $reason): void
    {
        DB::transaction(function () use ($order, $user, $reason) {
            $order = Order::where('id', $order->id)->lockForUpdate()->first();

            // Cancel any non-completed fulfillments
            foreach ($order->fulfillments as $fulfillment) {
                if (!in_array($fulfillment->status, ['completed', 'cancelled'])) {
                    $this->cancelFulfillment($fulfillment, $user, "Order Cancelled: {$reason}");
                }
            }

            $oldStatus = $order->order_status;
            $order->update([
                'order_status' => 'cancelled',
                'cancellation_reason' => $reason,
                'fulfillment_status' => 'unfulfilled',
                'delivery_status' => 'failed',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'cancelled',
                'comment' => "Order cancelled by {$user->name}. Reason: {$reason}",
                'changed_by_user_id' => $user->id,
            ]);
        });
    }

    /**
     * Synchronize overall Order fulfillment_status based on line-item counts
     */
    protected function syncOrderFulfillmentStatus(Order $order, User $user): void
    {
        $order->load('items');

        $totalOrdered = $order->items->sum('quantity');
        $totalFulfilled = $order->items->sum('fulfilled_quantity');

        $oldFulfillmentStatus = $order->fulfillment_status;
        $newFulfillmentStatus = 'unfulfilled';

        if ($totalFulfilled >= $totalOrdered && $totalOrdered > 0) {
            $newFulfillmentStatus = 'fulfilled';
        } elseif ($totalFulfilled > 0) {
            $newFulfillmentStatus = 'partially_fulfilled';
        }

        $updates = ['fulfillment_status' => $newFulfillmentStatus];

        // If order was pending, advance to confirmed/processing
        if ($order->order_status === 'pending' && $totalFulfilled > 0) {
            $updates['order_status'] = 'confirmed';
        }

        $order->update($updates);

        if ($oldFulfillmentStatus !== $newFulfillmentStatus) {
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldFulfillmentStatus,
                'to_status' => $newFulfillmentStatus,
                'comment' => "Fulfillment status updated to {$newFulfillmentStatus} ({$totalFulfilled}/{$totalOrdered} cans allocated)",
                'changed_by_user_id' => $user->id,
            ]);
        }
    }
}
