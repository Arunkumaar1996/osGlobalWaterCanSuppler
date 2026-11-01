<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * View current shopping cart.
     */
    public function index(Request $request): View
    {
        $branchId = session('active_branch_id', 1);
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
            $branchId
        );

        $subtotal = $cart->calculateSubtotal();
        $totalLitres = $cart->calculateTotalLitres();
        $hasExchanges = $cart->hasExchangeableCans();
        $exchangeableCount = $cart->totalExchangeableCansCount();
        $depositAmount = $cart->calculateDepositAmount();
        $totalPayable = $subtotal + $depositAmount;

        return view('cart.index', compact(
            'cart', 'subtotal', 'totalLitres', 'hasExchanges', 
            'exchangeableCount', 'depositAmount', 'totalPayable'
        ));
    }

    /**
     * Update customer can exchange preference (1:1 exchange vs new cans with deposit)
     */
    public function updateExchangeMode(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'has_empty_cans_to_exchange' => ['required', 'boolean'],
        ]);

        $branchId = session('active_branch_id', 1);
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
            $branchId
        );

        $cart->update(['has_empty_cans_to_exchange' => $validated['has_empty_cans_to_exchange']]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'has_empty_cans_to_exchange' => (bool) $cart->has_empty_cans_to_exchange,
                'deposit_amount' => $cart->calculateDepositAmount(),
                'subtotal' => $cart->calculateSubtotal(),
                'total_payable' => $cart->calculateSubtotal() + $cart->calculateDepositAmount(),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Can exchange preference updated.');
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $branchId = session('active_branch_id', 1);
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
            $branchId
        );

        $item = $this->cartService->addItem(
            $cart,
            $validated['product_id'],
            $validated['quantity'] ?? 1
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to your cart!',
                'cart_count' => $cart->items()->count(),
                'item_quantity' => $item->quantity,
            ]);
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return redirect()->route('cart.index')->with('success', "{$item->product->name} added to your cart!");
    }

    /**
     * Update item quantity in cart.
     */
    public function update(Request $request, int $itemId): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $branchId = session('active_branch_id', 1);
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
            $branchId
        );

        $updatedItem = $this->cartService->updateQuantity($cart, $itemId, $validated['quantity']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully.',
                'subtotal' => $cart->calculateSubtotal(),
            ]);
        }

        return back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request, int $itemId): RedirectResponse|JsonResponse
    {
        $branchId = session('active_branch_id', 1);
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
            $branchId
        );

        $this->cartService->removeItem($cart, $itemId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'subtotal' => $cart->calculateSubtotal(),
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }
}
