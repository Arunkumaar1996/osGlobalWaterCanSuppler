<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'start_time',
        'end_time',
        'cutoff_lead_minutes',
        'max_orders_capacity',
        'max_litres_capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cutoff_lead_minutes' => 'integer',
            'max_orders_capacity' => 'integer',
            'max_litres_capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function capacities(): HasMany
    {
        return $this->hasMany(DeliverySlotCapacity::class);
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        return date('g:i A', strtotime($this->start_time)) . ' - ' . date('g:i A', strtotime($this->end_time));
    }
}
