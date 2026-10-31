<?php

namespace App\Services;

use App\Models\CanMovement;
use App\Models\CustomerCanBalance;
use App\Models\CustomerProfile;
use App\Models\DeliveryTrip;
use App\Models\DeliveryTripStop;
use App\Models\Expense;
use App\Models\GoodsReceiptItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get Comprehensive Executive Business KPI Summary.
     */
    public function getExecutiveSummary(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        // 1. Revenue Analytics
        $orderQuery = Order::whereBetween('created_at', [$start, $end])
            ->whereNotIn('order_status', ['cancelled']);

        if ($branchId) {
            $orderQuery->where('branch_id', $branchId);
        }

        $grossSales = (float) (clone $orderQuery)->sum('total_amount');
        $ordersCount = (clone $orderQuery)->count();
        $deliveredOrdersCount = (clone $orderQuery)->where('delivery_status', 'delivered')->count();

        // Total payments collected
        $paymentQuery = Payment::whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'successful');

        if ($branchId) {
            $paymentQuery->where('branch_id', $branchId);
        }

        $cashCollected = (float) (clone $paymentQuery)->where('payment_method', 'cash')->sum('amount');
        $onlineCollected = (float) (clone $paymentQuery)->where('payment_method', 'razorpay')->sum('amount');
        $totalCollected = (float) (clone $paymentQuery)->sum('amount');

        // 2. Operating Expenses
        $expenseQuery = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $totalExpenses = (float) (clone $expenseQuery)->sum('amount');

        // 3. Procurement Cost (Cost of Goods Received)
        $procurementQuery = GoodsReceiptItem::whereBetween('created_at', [$start, $end]);
        if ($branchId) {
            $procurementQuery->whereHas('goodsReceipt', fn($q) => $q->where('branch_id', $branchId));
        }
        $procurementCost = (float) (clone $procurementQuery)->select(DB::raw('SUM(quantity_accepted * unit_cost) as cost'))->value('cost');

        // 4. Net Profit Calculation
        $netOperatingProfit = $grossSales - ($totalExpenses + $procurementCost);
        $profitMargin = $grossSales > 0 ? round(($netOperatingProfit / $grossSales) * 100, 2) : 0.00;

        // 5. Volume Analysis (Total water volume in Litres)
        $volumeQuery = OrderItem::whereHas('order', function ($q) use ($start, $end, $branchId) {
            $q->whereBetween('created_at', [$start, $end])
              ->whereNotIn('order_status', ['cancelled']);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        });
        $cansDelivered = (int) (clone $volumeQuery)->sum('quantity');

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'gross_sales' => $grossSales,
            'orders_count' => $ordersCount,
            'delivered_orders_count' => $deliveredOrdersCount,
            'total_collected' => $totalCollected,
            'cash_collected' => $cashCollected,
            'online_collected' => $onlineCollected,
            'total_expenses' => $totalExpenses,
            'procurement_cost' => $procurementCost,
            'net_operating_profit' => $netOperatingProfit,
            'profit_margin' => $profitMargin,
            'total_cans_ordered' => $cansDelivered,
        ];
    }

    /**
     * Profit & Loss (P&L) Statement Breakdown.
     */
    public function getProfitAndLossStatement(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        // Revenue Components
        $orderQuery = Order::whereBetween('created_at', [$start, $end])
            ->whereNotIn('order_status', ['cancelled']);
        if ($branchId) {
            $orderQuery->where('branch_id', $branchId);
        }

        $waterSalesRevenue = (float) (clone $orderQuery)->sum('subtotal_amount');
        $taxCollected = (float) (clone $orderQuery)->sum('tax_amount');
        $deliveryFeesCollected = (float) (clone $orderQuery)->sum('delivery_charge');

        // Lost can penalties collected into financial ledger
        $penaltyIncomeQuery = DB::table('customer_ledger')
            ->whereBetween('customer_ledger.created_at', [$start, $end])
            ->where(function ($q) {
                $q->where('transaction_type', 'penalty')
                  ->orWhere(function ($sq) {
                      $sq->where('transaction_type', 'debit_adjustment')
                         ->where('notes', 'LIKE', '%penalty%');
                  });
            });

        if ($branchId) {
            $penaltyIncomeQuery->join('customer_profiles', 'customer_profiles.id', '=', 'customer_ledger.customer_profile_id')
                ->where('customer_profiles.branch_id', $branchId);
        }

        $penaltyIncome = (float) $penaltyIncomeQuery->sum('debit_amount');

        $totalOperatingIncome = $waterSalesRevenue + $deliveryFeesCollected + $penaltyIncome;

        // Cost of Goods Sold (Procurement)
        $procurementQuery = GoodsReceiptItem::whereBetween('created_at', [$start, $end]);
        if ($branchId) {
            $procurementQuery->whereHas('goodsReceipt', fn($q) => $q->where('branch_id', $branchId));
        }
        $cogs = (float) (clone $procurementQuery)->select(DB::raw('SUM(quantity_accepted * unit_cost) as cost'))->value('cost');

        $grossProfit = $totalOperatingIncome - $cogs;

        // Categorized Operating Expenses
        $expenseQuery = Expense::with('category')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }

        $expensesByCategory = (clone $expenseQuery)
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->with('category')
            ->get()
            ->map(fn($e) => [
                'category_name' => $e->category?->name ?? 'Uncategorized',
                'amount' => (float) $e->total,
            ]);

        $totalOperatingExpenses = (float) $expensesByCategory->sum('amount');
        $netProfit = $grossProfit - $totalOperatingExpenses;
        $netMarginPercent = $totalOperatingIncome > 0 ? round(($netProfit / $totalOperatingIncome) * 100, 2) : 0.00;

        return [
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'income' => [
                'water_sales' => $waterSalesRevenue,
                'delivery_charges' => $deliveryFeesCollected,
                'lost_can_penalties' => $penaltyIncome,
                'taxes_collected' => $taxCollected,
                'total_income' => $totalOperatingIncome,
            ],
            'cogs' => [
                'procurement_cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'gross_margin_percent' => $totalOperatingIncome > 0 ? round(($grossProfit / $totalOperatingIncome) * 100, 2) : 0.00,
            ],
            'expenses' => [
                'by_category' => $expensesByCategory,
                'total_expenses' => $totalOperatingExpenses,
            ],
            'net_profit' => $netProfit,
            'net_margin_percent' => $netMarginPercent,
        ];
    }

    /**
     * Container Fleet Shrinkage & Asset Health Report.
     */
    public function getContainerFleetReport(?int $branchId = null): array
    {
        // 1. Warehouse Stocks of Containers
        $stockQuery = WarehouseStock::query();
        if ($branchId) {
            $stockQuery->whereHas('warehouse', fn($q) => $q->where('branch_id', $branchId));
        }

        $warehouseFull = (int) (clone $stockQuery)->sum('full_stock');
        $warehouseEmpty = (int) (clone $stockQuery)->sum('empty_stock');
        $underCleaning = (int) (clone $stockQuery)->sum('under_cleaning');
        $damagedWarehouse = (int) (clone $stockQuery)->sum('damaged_stock');
        $totalWarehouseCans = $warehouseFull + $warehouseEmpty + $underCleaning + $damagedWarehouse;

        // 2. Customer Circulation
        $balanceQuery = CustomerCanBalance::query();
        if ($branchId) {
            $balanceQuery->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
        }
        $customerHolding = (int) (clone $balanceQuery)->sum('held_empty_cans');

        // 3. Lost / Broken Container Movements
        $movementsQuery = CanMovement::whereIn('movement_type', ['lost_by_customer', 'damaged']);
        if ($branchId) {
            $movementsQuery->where('branch_id', $branchId);
        }
        $lostCount = (int) (clone $movementsQuery)->sum('quantity');

        // Estimated Replacement Valuation (Assume average unit container cost ₹150)
        $avgContainerCost = 150.00;
        $totalFleetSize = $totalWarehouseCans + $customerHolding + $lostCount;
        $shrinkagePercent = $totalFleetSize > 0 ? round(($lostCount / $totalFleetSize) * 100, 2) : 0.00;

        return [
            'warehouse_full' => $warehouseFull,
            'warehouse_empty' => $warehouseEmpty,
            'under_cleaning' => $underCleaning,
            'warehouse_damaged' => $damagedWarehouse,
            'total_warehouse' => $totalWarehouseCans,
            'customer_holding' => $customerHolding,
            'lost_damaged_count' => $lostCount,
            'total_fleet_size' => $totalFleetSize,
            'shrinkage_percent' => $shrinkagePercent,
            'estimated_replacement_cost' => $lostCount * $avgContainerCost,
        ];
    }

    /**
     * Delivery Fleet & Staff Driver Efficiency Metrics.
     */
    public function getFleetEfficiencyReport(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $tripsQuery = DeliveryTrip::with(['staff.user', 'partner', 'stops'])
            ->whereBetween('trip_date', [$start->toDateString(), $end->toDateString()]);

        if ($branchId) {
            $tripsQuery->where('branch_id', $branchId);
        }

        $trips = $tripsQuery->get();

        $totalTrips = $trips->count();
        $totalStops = $trips->sum('total_orders_count');
        $deliveredStops = $trips->sum('delivered_orders_count');
        $failedStops = $trips->sum('failed_orders_count');
        $totalCashCollected = (float) $trips->sum('total_cash_collected');
        $totalEmptiesReturned = (int) $trips->sum('total_empty_cans_returned');

        $completionRate = $totalStops > 0 ? round(($deliveredStops / $totalStops) * 100, 2) : 0.00;

        // Group efficiency by driver / partner
        $driverMetrics = $trips->groupBy(function ($trip) {
            if ($trip->driver_type === 'staff') {
                return $trip->staff?->user?->name ?? 'Staff Driver';
            }
            return $trip->partner?->name ?? '3rd-Party Partner';
        })->map(function ($driverTrips, $name) {
            $stops = $driverTrips->sum('total_orders_count');
            $delivered = $driverTrips->sum('delivered_orders_count');
            $failed = $driverTrips->sum('failed_orders_count');
            $cash = (float) $driverTrips->sum('total_cash_collected');

            return [
                'name' => $name,
                'trips_count' => $driverTrips->count(),
                'total_stops' => $stops,
                'delivered_stops' => $delivered,
                'failed_stops' => $failed,
                'cash_collected' => $cash,
                'success_rate' => $stops > 0 ? round(($delivered / $stops) * 100, 1) : 0.00,
            ];
        })->values();

        return [
            'total_trips' => $totalTrips,
            'total_stops' => $totalStops,
            'delivered_stops' => $deliveredStops,
            'failed_stops' => $failedStops,
            'total_cash_collected' => $totalCashCollected,
            'total_empties_returned' => $totalEmptiesReturned,
            'completion_rate' => $completionRate,
            'drivers' => $driverMetrics,
        ];
    }
}
