<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_number',
        'branch_id',
        'delivery_slot_id',
        'trip_date',
        'driver_type',
        'staff_profile_id',
        'delivery_partner_id',
        'vehicle_number',
        'status',
        'started_at',
        'completed_at',
        'total_orders_count',
        'total_litres_payload',
        'cash_collected',
        'empty_cans_collected',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_litres_payload' => 'decimal:2',
            'cash_collected' => 'decimal:2',
            'total_orders_count' => 'integer',
            'empty_cans_collected' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function deliveryPartner(): BelongsTo
    {
        return $this->belongsTo(DeliveryPartner::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(DeliveryTripStop::class)->orderBy('sequence_order');
    }

    public function getDriverNameAttribute(): string
    {
        if ($this->driver_type === 'staff') {
            return $this->staffProfile?->user?->name ?? 'Staff Driver';
        }

        return $this->deliveryPartner?->name ?? 'Delivery Partner';
    }

    public function getDriverPhoneAttribute(): ?string
    {
        if ($this->driver_type === 'staff') {
            return $this->staffProfile?->user?->mobile;
        }

        return $this->deliveryPartner?->contact_phone;
    }
}
