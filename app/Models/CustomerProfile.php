<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'customer_type',
        'company_name',
        'gst_number',
        'outstanding_financial_balance',
        'credit_limit',
        'allow_pay_later',
        'referral_code',
        'referred_by_user_id',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_financial_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'allow_pay_later' => 'boolean',
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

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function canBalances(): HasMany
    {
        return $this->hasMany(CustomerCanBalance::class);
    }

    public function canMovements(): HasMany
    {
        return $this->hasMany(CanMovement::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
