<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_number',
        'customer_profile_id',
        'branch_id',
        'delivery_address_id',
        'delivery_slot_id',
        'frequency',
        'custom_days',
        'status',
        'start_date',
        'end_date',
        'next_delivery_date',
        'last_generated_date',
        'payment_method',
        'customer_notes',
        'pause_reason',
    ];

    protected function casts(): array
    {
        return [
            'custom_days' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_delivery_date' => 'date',
            'last_generated_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class, 'customer_profile_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }

    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class, 'delivery_slot_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily' => 'Every Day (Daily)',
            'alternate_days' => 'Alternate Days',
            'weekly' => 'Once a Week',
            'monthly' => 'Once a Month',
            'custom_days' => 'Custom Days (' . implode(', ', array_map('ucfirst', $this->custom_days ?? [])) . ')',
            default => ucfirst(str_replace('_', ' ', $this->frequency)),
        };
    }
}
