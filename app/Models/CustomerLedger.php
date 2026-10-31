<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomerLedger extends Model
{
    use HasFactory;

    protected $table = 'customer_ledger';
    public $timestamps = false;

    protected $fillable = [
        'customer_profile_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'debit_amount',
        'credit_amount',
        'running_balance',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'debit_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class, 'customer_profile_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
