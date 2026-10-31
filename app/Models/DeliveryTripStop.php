<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTripStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_trip_id',
        'order_id',
        'sequence_order',
        'status',
        'delivery_otp',
        'otp_verified_at',
        'empty_cans_collected',
        'cash_collected',
        'failure_reason',
        'customer_signature_or_photo',
        'delivered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sequence_order' => 'integer',
            'empty_cans_collected' => 'integer',
            'cash_collected' => 'decimal:2',
            'otp_verified_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(DeliveryTrip::class, 'delivery_trip_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
