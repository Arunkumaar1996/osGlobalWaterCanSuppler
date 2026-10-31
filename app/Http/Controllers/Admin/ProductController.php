<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CanType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display listing of products with filters
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'canType'])
            ->withCount('priceHistories');

        if ($request->filled('q')) {
            $searchTerm = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('sku', 'LIKE', $searchTerm);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show form for creating a new product
     */
    public function create(): View
    {
        $categories = ProductCategory::orderBy('name')->get();
        $canTypes = CanType::orderBy('capacity_litres')->get();

        return view('admin.products.create', compact('categories', 'canTypes'));
    }

    /**
     * Store newly created product
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'can_type_id' => ['nullable', 'exists:can_types,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity_value' => ['required', 'numeric', 'min:0.1'],
            'capacity_unit' => ['required', 'string', 'max:20'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'tax_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_tax_inclusive' => ['nullable', 'boolean'],
            'min_order_quantity' => ['required', 'integer', 'min:1'],
            'max_order_quantity' => ['required', 'integer', 'gte:min_order_quantity'],
            'requires_can_exchange' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_tax_inclusive'] = $request->boolean('is_tax_inclusive', true);
        $validated['requires_can_exchange'] = $request->boolean('requires_can_exchange', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['tax_rate_percent'] = $validated['tax_rate_percent'] ?? 18.00;

        $product = $this->productService->createProduct($validated, $request->user());

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' created successfully with SKU {$product->sku}.");
    }

    /**
     * Show form for editing product
     */
    public function edit(Product $product): View
    {
        $categories = ProductCategory::orderBy('name')->get();
        $canTypes = CanType::orderBy('capacity_litres')->get();
        $product->load(['category', 'canType', 'priceHistories.changedBy']);

        return view('admin.products.edit', compact('product', 'categories', 'canTypes'));
    }

    /**
     * Update product and log price change history if price changed
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'can_type_id' => ['nullable', 'exists:can_types,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity_value' => ['required', 'numeric', 'min:0.1'],
            'capacity_unit' => ['required', 'string', 'max:20'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'tax_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_tax_inclusive' => ['nullable', 'boolean'],
            'min_order_quantity' => ['required', 'integer', 'min:1'],
            'max_order_quantity' => ['required', 'integer', 'gte:min_order_quantity'],
            'requires_can_exchange' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'price_change_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_tax_inclusive'] = $request->boolean('is_tax_inclusive', true);
        $validated['requires_can_exchange'] = $request->boolean('requires_can_exchange', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['tax_rate_percent'] = $validated['tax_rate_percent'] ?? 18.00;

        $reason = $request->input('price_change_reason');
        unset($validated['price_change_reason']);

        $this->productService->updateProduct($product, $validated, $request->user(), $reason);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' updated successfully.");
    }

    /**
     * Toggle product active status
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $newStatus = $this->productService->toggleStatus($product);
        $statusLabel = $newStatus ? 'activated' : 'deactivated';

        return back()->with('success', "Product '{$product->name}' has been {$statusLabel}.");
    }

    /**
     * Show price history audit trail for a product
     */
    public function priceHistory(Product $product): View
    {
        $histories = $product->priceHistories()
            ->with('changedBy')
            ->orderBy('effective_from', 'desc')
            ->paginate(20);

        return view('admin.products.price-history', compact('product', 'histories'));
    }
}
