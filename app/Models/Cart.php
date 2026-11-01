<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'branch_id',
        'has_empty_cans_to_exchange',
    ];

    protected function casts(): array
    {
        return [
            'has_empty_cans_to_exchange' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Compute subtotal safely on the server side
     */
    public function calculateSubtotal(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->quantity * $item->product->selling_price;
        });
    }

    /**
     * Compute total water litres in cart (for capacity checks)
     */
    public function calculateTotalLitres(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->quantity * $item->product->capacity_value;
        });
    }

    /**
     * Determine if cart contains containers requiring 1:1 empty can exchange
     */
    public function hasExchangeableCans(): bool
    {
        return $this->items->contains(function ($item) {
            return $item->product->requires_can_exchange;
        });
    }

    /**
     * Total quantity of exchangeable cans in the cart
     */
    public function totalExchangeableCansCount(): int
    {
        return (int) $this->items->filter(function ($item) {
            return $item->product->requires_can_exchange;
        })->sum('quantity');
    }

    /**
     * Compute refundable container deposit amount for new customers
     * (only applied when customer does not have empty cans to return)
     */
    public function calculateDepositAmount(): float
    {
        if ($this->has_empty_cans_to_exchange) {
            return 0.00;
        }

        return (float) $this->items->filter(function ($item) {
            return $item->product->requires_can_exchange;
        })->sum(function ($item) {
            $depositPerCan = (float) ($item->product->canType?->deposit_amount ?? 150.00);
            return $item->quantity * $depositPerCan;
        });
    }
}
