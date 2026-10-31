<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CanMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_profile_id',
        'branch_id',
        'can_type_id',
        'movement_type',
        'cans_delivered_full',
        'cans_collected_empty',
        'missing_cans',
        'balance_after_movement',
        'reference_type',
        'reference_id',
        'recorded_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cans_delivered_full' => 'integer',
            'cans_collected_empty' => 'integer',
            'missing_cans' => 'integer',
            'balance_after_movement' => 'integer',
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

    public function canType(): BelongsTo
    {
        return $this->belongsTo(CanType::class, 'can_type_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
