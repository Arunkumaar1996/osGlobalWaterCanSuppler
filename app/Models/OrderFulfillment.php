<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderFulfillment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fulfillment_number',
        'order_id',
        'source_type',
        'warehouse_id',
        'supplier_id',
        'status',
        'assigned_by_user_id',
        'allocated_at',
        'dispatched_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allocated_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderFulfillmentItem::class);
    }

    public function manifests(): BelongsToMany
    {
        return $this->belongsToMany(DispatchManifest::class, 'dispatch_manifest_fulfillments');
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}
