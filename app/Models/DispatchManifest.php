<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DispatchManifest extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifest_number',
        'branch_id',
        'warehouse_id',
        'status',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'generated_by_user_id',
        'generated_at',
        'dispatched_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'dispatched_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    public function fulfillments(): BelongsToMany
    {
        return $this->belongsToMany(OrderFulfillment::class, 'dispatch_manifest_fulfillments');
    }
}
