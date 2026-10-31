<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierLedger;
use App\Models\SupplierPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SupplierService
{
    /**
     * Create a new Purchase Order to a supplier
     */
    public function createPurchaseOrder(
        int $supplierId,
        int $branchId,
        int $warehouseId,
        array $items,
        string $expectedDate,
        User $admin,
        ?string $notes = null
    ): PurchaseOrder {
        return DB::transaction(function () use (
            $supplierId, $branchId, $warehouseId, $items, $expectedDate, $admin, $notes
        ) {
            $year = date('Y');
            $latestPO = PurchaseOrder::where('po_number', 'LIKE', "PO-{$year}-%")->latest('id')->first();
            $seq = $latestPO ? ((int) substr($latestPO->po_number, -4)) + 1 : 1;
            $poNumber = sprintf("PO-%s-%04d", $year, $seq);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $supplierId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'status' => 'issued',
                'total_amount' => 0.00,
                'tax_amount' => 0.00,
                'expected_delivery_date' => Carbon::parse($expectedDate)->format('Y-m-d'),
                'created_by_user_id' => $admin->id,
                'notes' => $notes,
            ]);

            $total = 0.00;
            foreach ($items as $item) {
                $subtotal = (float) ($item['quantity'] * $item['unit_price']);
                $total += $subtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'ordered_quantity' => $item['quantity'],
                    'received_quantity' => 0,
                    'rejected_quantity' => 0,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $po->update(['total_amount' => $total]);

            return $po->load(['items.product', 'supplier', 'warehouse']);
        });
    }

    /**
     * Record partial or full goods receipt (GRN) with inspection of damaged/accepted stock
     */
    public function recordGoodsReceipt(
        PurchaseOrder $po,
        array $receiptItems,
        User $staff,
        ?string $challanNo = null,
        ?string $remarks = null
    ): GoodsReceipt {
        return DB::transaction(function () use ($po, $receiptItems, $staff, $challanNo, $remarks) {
            $yearMonth = date('Ym');
            $latestGRN = GoodsReceipt::where('grn_number', 'LIKE', "GRN-{$yearMonth}-%")->latest('id')->first();
            $seq = $latestGRN ? ((int) substr($latestGRN->grn_number, -4)) + 1 : 1;
            $grnNumber = sprintf("GRN-%s-%04d", $yearMonth, $seq);

            $grn = GoodsReceipt::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $po->id,
                'warehouse_id' => $po->warehouse_id,
                'received_by_user_id' => $staff->id,
                'received_at' => now(),
                'supplier_challan_no' => $challanNo,
                'remarks' => $remarks,
            ]);

            $acceptedValueTotal = 0.00;
            $allCompleted = true;

            foreach ($receiptItems as $itemData) {
                $poItem = PurchaseOrderItem::where('id', $itemData['purchase_order_item_id'])
                    ->where('purchase_order_id', $po->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $accepted = max(0, (int) $itemData['quantity_accepted']);
                $damaged = max(0, (int) $itemData['quantity_damaged']);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'quantity_accepted' => $accepted,
                    'quantity_damaged' => $damaged,
                    'unit_cost' => $poItem->unit_price,
                ]);

                $newReceivedTotal = $poItem->received_quantity + $accepted;
                $newRejectedTotal = $poItem->rejected_quantity + $damaged;

                $poItem->update([
                    'received_quantity' => $newReceivedTotal,
                    'rejected_quantity' => $newRejectedTotal,
                ]);

                if (($newReceivedTotal + $newRejectedTotal) < $poItem->ordered_quantity) {
                    $allCompleted = false;
                }

                $acceptedValueTotal += ($accepted * (float) $poItem->unit_price);

                // Add accepted stock to Warehouse inventory & ledger
                if ($accepted > 0) {
                    app(InventoryService::class)->receivePurchasedStock(
                        warehouseId: $po->warehouse_id,
                        productId: $poItem->product_id,
                        acceptedQuantity: $accepted,
                        user: $staff,
                        reference: $grn,
                        notes: "Stock received via GRN {$grnNumber}"
                    );
                }
            }

            // Update PO Status
            $po->update([
                'status' => $allCompleted ? 'completed' : 'partially_received',
            ]);

            // Generate Supplier Invoice for accepted quantities
            if ($acceptedValueTotal > 0) {
                $invNumber = 'SINV-' . date('Ymd') . '-' . rand(1000, 9999);
                $supplier = Supplier::where('id', $po->supplier_id)->lockForUpdate()->first();

                SupplierInvoice::create([
                    'invoice_number' => $invNumber,
                    'supplier_id' => $supplier->id,
                    'purchase_order_id' => $po->id,
                    'invoice_date' => Carbon::today(),
                    'due_date' => Carbon::today()->addDays(30),
                    'subtotal' => $acceptedValueTotal,
                    'tax_amount' => 0.00,
                    'total_amount' => $acceptedValueTotal,
                    'paid_amount' => 0.00,
                    'status' => 'unpaid',
                ]);

                // Post Credit Liability to Supplier Ledger
                $newPayableBalance = (float) $supplier->outstanding_payable_balance + $acceptedValueTotal;

                SupplierLedger::create([
                    'supplier_id' => $supplier->id,
                    'transaction_type' => 'invoice_billed',
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $po->id,
                    'debit_amount' => 0.00,
                    'credit_amount' => $acceptedValueTotal,
                    'running_balance' => $newPayableBalance,
                    'notes' => "GRN {$grnNumber} accepted stock billed (Invoice: {$invNumber})",
                ]);

                $supplier->update(['outstanding_payable_balance' => $newPayableBalance]);
            }

            return $grn->load(['items.product', 'purchaseOrder']);
        });
    }

    /**
     * Record a payment made to a supplier and settle ledger liability
     */
    public function recordSupplierPayment(
        int $supplierId,
        float $amount,
        string $paymentMode,
        ?string $transactionRef = null,
        ?string $paymentDate = null,
        ?string $notes = null
    ): SupplierPayment {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($supplierId, $amount, $paymentMode, $transactionRef, $paymentDate, $notes) {
            $supplier = Supplier::where('id', $supplierId)->lockForUpdate()->firstOrFail();

            $payNumber = 'SPAY-' . date('Ym') . '-' . rand(1000, 9999);

            $payment = SupplierPayment::create([
                'payment_number' => $payNumber,
                'supplier_id' => $supplier->id,
                'amount' => $amount,
                'payment_mode' => $paymentMode,
                'transaction_reference' => $transactionRef,
                'payment_date' => $paymentDate ? Carbon::parse($paymentDate)->format('Y-m-d') : Carbon::today()->format('Y-m-d'),
                'notes' => $notes,
            ]);

            // Post Debit to Supplier Ledger (Reduces liability)
            $newBalance = (float) $supplier->outstanding_payable_balance - $amount;

            SupplierLedger::create([
                'supplier_id' => $supplier->id,
                'transaction_type' => 'payment_made',
                'reference_type' => SupplierPayment::class,
                'reference_id' => $payment->id,
                'debit_amount' => $amount,
                'credit_amount' => 0.00,
                'running_balance' => $newBalance,
                'notes' => "Payment made via {$paymentMode} (Ref: {$transactionRef})",
            ]);

            $supplier->update(['outstanding_payable_balance' => $newBalance]);

            return $payment;
        });
    }
}
