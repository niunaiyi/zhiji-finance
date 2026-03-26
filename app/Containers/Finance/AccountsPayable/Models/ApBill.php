<?php

namespace App\Containers\Finance\AccountsPayable\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApBill extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'period_id',
        'bill_no',
        'bill_date',
        'supplier_id',
        'amount',
        'settled_amount',
        'balance',
        'status',
        'is_estimate',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'amount' => 'decimal:2',
        'settled_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_estimate' => 'boolean',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\Period::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\AuxItem::class, 'supplier_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(ApSettlement::class);
    }

    public function scopeBySupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeEstimate($query)
    {
        return $query->where('is_estimate', true);
    }
}
