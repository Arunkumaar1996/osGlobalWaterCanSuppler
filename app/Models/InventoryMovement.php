<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'can_type_id',
        'user_id',
        'movement_type',
        'state_from',
        'state_to',
        'quantity',
        'full_stock_after',
        'empty_stock_after',
        'under_cleaning_after',
        'damaged_stock_after',
        'reserved_stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'full_stock_after' => 'integer',
            'empty_stock_after' => 'integer',
            'under_cleaning_after' => 'integer',
            'damaged_stock_after' => 'integer',
            'reserved_stock_after' => 'integer',
            'created_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
