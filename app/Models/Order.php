<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'idempotency_key',
        'customer_profile_id',
        'branch_id',
        'delivery_address_id',
        'snapshot_address',
        'order_status',
        'fulfillment_status',
        'payment_status',
        'delivery_status',
        'subtotal_amount',
        'tax_amount',
        'delivery_charge',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'previous_outstanding_at_order',
        'payment_method_preferred',
        'delivery_date',
        'delivery_slot_id',
        'customer_notes',
        'admin_notes',
        'cancellation_reason',
        'is_subscription_order',
        'subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_address' => 'array',
            'delivery_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'previous_outstanding_at_order' => 'decimal:2',
            'is_subscription_order' => 'boolean',
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
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(OrderFulfillment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tripStops(): HasMany
    {
        return $this->hasMany(DeliveryTripStop::class);
    }

    public function latestTripStop()
    {
        return $this->hasOne(DeliveryTripStop::class)->latestOfMany();
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
