<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_person',
        'phone',
        'email',
        'gst_number',
        'pan_number',
        'address',
        'city',
        'state',
        'pincode',
        'bank_name',
        'bank_account_no',
        'bank_ifsc_code',
        'outstanding_payable_balance',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_payable_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_products')
            ->withPivot('supplier_sku', 'purchase_price', 'lead_time_hours', 'is_preferred')
            ->withTimestamps();
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(SupplierLedger::class);
    }
}
