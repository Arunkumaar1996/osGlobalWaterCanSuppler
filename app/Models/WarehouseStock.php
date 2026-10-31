<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'can_type_id',
        'full_stock',
        'empty_stock',
        'under_cleaning',
        'damaged_stock',
        'reserved_stock',
        'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'full_stock' => 'integer',
            'empty_stock' => 'integer',
            'under_cleaning' => 'integer',
            'damaged_stock' => 'integer',
            'reserved_stock' => 'integer',
            'reorder_level' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function canType(): BelongsTo
    {
        return $this->belongsTo(CanType::class);
    }

    /**
     * Unreserved full stock available for immediate ordering or dispatch
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->full_stock - $this->reserved_stock);
    }

    /**
     * Total physical bottles belonging to this SKU in this warehouse across all states
     */
    public function getTotalPhysicalCansAttribute(): int
    {
        return $this->full_stock + $this->empty_stock + $this->under_cleaning + $this->damaged_stock;
    }

    /**
     * Check if available stock has dipped below reorder threshold
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->available_stock <= $this->reorder_level;
    }
}
