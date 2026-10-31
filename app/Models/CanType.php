<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity_litres',
        'material',
        'deposit_amount',
        'is_reusable',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity_litres' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'is_reusable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customerBalances(): HasMany
    {
        return $this->hasMany(CustomerCanBalance::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CanMovement::class);
    }
}
