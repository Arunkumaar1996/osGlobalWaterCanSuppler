<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryPincode extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_zone_id',
        'pincode',
        'area_name',
        'delivery_charge',
        'min_order_amount',
        'is_serviceable',
    ];

    protected function casts(): array
    {
        return [
            'delivery_charge' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_serviceable' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }
}
