<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        protected SupplierService $supplierService
    ) {}

    /**
     * Display listing of suppliers
     */
    public function index(Request $request): View
    {
        $query = Supplier::withCount(['products', 'purchaseOrders']);

        if ($request->filled('q')) {
            $searchTerm = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('company_name', 'LIKE', $searchTerm)
                  ->orWhere('contact_person', 'LIKE', $searchTerm)
                  ->orWhere('phone', 'LIKE', $searchTerm)
                  ->orWhere('email', 'LIKE', $searchTerm);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $suppliers = $query->orderBy('company_name')->paginate(15)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Show form for creating a new supplier
     */
    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    /**
     * Store newly created supplier
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_ifsc_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['outstanding_payable_balance'] = 0.00;

        $supplier = Supplier::create($validated);

        return redirect()->route('admin.suppliers.show', $supplier)
            ->with('success', "Supplier '{$supplier->company_name}' registered successfully.");
    }

    /**
     * Show supplier details, mapped products, ledger, and POs
     */
    public function show(Supplier $supplier): View
    {
        $supplier->load([
            'supplierProducts.product',
            'purchaseOrders' => fn($q) => $q->latest()->limit(10),
            'ledger' => fn($q) => $q->latest()->limit(20),
            'payments' => fn($q) => $q->latest()->limit(10),
        ]);

        $allProducts = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.suppliers.show', compact('supplier', 'allProducts'));
    }

    /**
     * Show form for editing supplier
     */
    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update supplier details
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_ifsc_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier->update($validated);

        return redirect()->route('admin.suppliers.show', $supplier)
            ->with('success', "Supplier '{$supplier->company_name}' updated successfully.");
    }

    /**
     * Map a product with price and lead time to the supplier
     */
    public function mapProduct(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'supplier_sku' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'lead_time_hours' => ['required', 'integer', 'min:1'],
            'is_preferred' => ['nullable', 'boolean'],
        ]);

        $validated['is_preferred'] = $request->boolean('is_preferred', false);

        SupplierProduct::updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'product_id' => $validated['product_id'],
            ],
            [
                'supplier_sku' => $validated['supplier_sku'] ?? null,
                'purchase_price' => $validated['purchase_price'],
                'lead_time_hours' => $validated['lead_time_hours'],
                'is_preferred' => $validated['is_preferred'],
            ]
        );

        return back()->with('success', 'Product mapped to supplier successfully.');
    }

    /**
     * Unmap a product from supplier
     */
    public function unmapProduct(Supplier $supplier, SupplierProduct $supplierProduct): RedirectResponse
    {
        if ($supplierProduct->supplier_id === $supplier->id) {
            $supplierProduct->delete();
        }

        return back()->with('success', 'Product mapping removed.');
    }

    /**
     * Record a payment made to a supplier (debits supplier ledger balance)
     */
    public function recordPayment(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_mode' => ['required', 'string', 'in:bank_transfer,upi,cheque,cash,neft_rtgs'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->supplierService->recordSupplierPayment(
            supplierId: $supplier->id,
            amount: (float) $validated['amount'],
            paymentMode: $validated['payment_mode'],
            transactionRef: $validated['transaction_reference'] ?? null,
            paymentDate: $validated['payment_date'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return back()->with('success', "Payment of ₹" . number_format($validated['amount'], 2) . " recorded and debited from supplier balance.");
    }
}
