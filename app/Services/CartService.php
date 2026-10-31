<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Retrieve or create active cart for user or guest session
     */
    public function getOrCreateCart(?User $user, ?string $sessionId, int $branchId): Cart
    {
        if ($user) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id, 'branch_id' => $branchId],
                ['session_id' => $sessionId]
            );
        } else {
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId, 'branch_id' => $branchId],
                ['user_id' => null]
            );
        }

        return $cart->load(['items.product.canType']);
    }

    /**
     * Add product to cart with server-side validation
     */
    public function addItem(Cart $cart, int $productId, int $quantity = 1): CartItem
    {
        $product = Product::where('id', $productId)->where('is_active', true)->firstOrFail();

        $quantity = max(1, min($quantity, $product->max_order_quantity));

        return DB::transaction(function () use ($cart, $product, $quantity) {
            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $newQty = min($item->quantity + $quantity, $product->max_order_quantity);
                $item->update(['quantity' => $newQty]);
                return $item;
            }

            return CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        });
    }

    /**
     * Update item quantity in cart
     */
    public function updateQuantity(Cart $cart, int $cartItemId, int $quantity): ?CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->first();

        if (! $item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $maxQty = $item->product?->max_order_quantity ?? 50;
        $quantity = min($quantity, $maxQty);

        $item->update(['quantity' => $quantity]);
        return $item;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Cart $cart, int $cartItemId): bool
    {
        return (bool) CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->delete();
    }

    /**
     * Clear all items from cart
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }
}
