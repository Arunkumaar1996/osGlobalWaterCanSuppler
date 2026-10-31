<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCanBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_profile_id',
        'can_type_id',
        'held_cans_count',
    ];

    protected function casts(): array
    {
        return [
            'held_cans_count' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class, 'customer_profile_id');
    }

    public function canType(): BelongsTo
    {
        return $this->belongsTo(CanType::class, 'can_type_id');
    }
}
