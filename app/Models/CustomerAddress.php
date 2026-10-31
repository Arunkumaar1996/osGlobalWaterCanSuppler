<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_profile_id',
        'address_type',
        'contact_person_name',
        'contact_person_mobile',
        'house_flat_no',
        'building_apartment_name',
        'street_address',
        'landmark',
        'area',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'delivery_instructions',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function customerProfile(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->house_flat_no,
            $this->building_apartment_name,
            $this->street_address,
            $this->landmark ? 'Near '.$this->landmark : null,
            $this->area,
            $this->city,
            $this->state.' - '.$this->pincode,
        ]);

        return implode(', ', $parts);
    }
}
