<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'designation',
        'vehicle_number',
        'driving_license_no',
        'emergency_contact',
        'current_cash_in_hand',
        'is_available_for_delivery',
    ];

    protected function casts(): array
    {
        return [
            'current_cash_in_hand' => 'decimal:2',
            'is_available_for_delivery' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
