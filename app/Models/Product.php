<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'can_type_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'capacity_value',
        'capacity_unit',
        'selling_price',
        'tax_rate_percent',
        'is_tax_inclusive',
        'min_order_quantity',
        'max_order_quantity',
        'requires_can_exchange',
        'image_path',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity_value' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'tax_rate_percent' => 'decimal:2',
            'is_tax_inclusive' => 'boolean',
            'requires_can_exchange' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function canType(): BelongsTo
    {
        return $this->belongsTo(CanType::class, 'can_type_id');
    }

    public function priceHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductPriceHistory::class)->orderBy('effective_from', 'desc');
    }

    public function supplierProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->selling_price, 2);
    }
}
