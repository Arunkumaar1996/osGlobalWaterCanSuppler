<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySlotCapacity extends Model
{
    use HasFactory;

    protected $table = 'delivery_slot_capacity';

    protected $fillable = [
        'delivery_slot_id',
        'date',
        'reserved_orders_count',
        'reserved_litres_volume',
        'is_blocked',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'reserved_orders_count' => 'integer',
            'reserved_litres_volume' => 'integer',
            'is_blocked' => 'boolean',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class, 'delivery_slot_id');
    }
}
