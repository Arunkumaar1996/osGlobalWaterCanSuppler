<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name_snapshot',
        'can_type_id',
        'quantity',
        'fulfilled_quantity',
        'delivered_quantity',
        'unit_price',
        'tax_rate_percent',
        'tax_amount',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'fulfilled_quantity' => 'integer',
            'delivered_quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'tax_rate_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function canType(): BelongsTo
    {
        return $this->belongsTo(CanType::class);
    }

    public function fulfillmentItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderFulfillmentItem::class);
    }

    public function getUnfulfilledQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->fulfilled_quantity);
    }
}
