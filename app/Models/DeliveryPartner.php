<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'contact_person',
        'contact_phone',
        'contact_email',
        'billing_type',
        'rate_per_unit',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_unit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(DeliveryTrip::class);
    }
}
