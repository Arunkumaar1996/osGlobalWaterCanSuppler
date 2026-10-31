<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $table = 'supplier_products';

    protected $fillable = [
        'supplier_id',
        'product_id',
        'supplier_sku',
        'purchase_price',
        'lead_time_hours',
        'is_preferred',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'lead_time_hours' => 'integer',
            'is_preferred' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
