<?php

namespace App\Containers\Finance\AccountsReceivable\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArBill extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'period_id',
        'bill_no',
        'bill_date',
        'customer_id',
        'amount',
        'settled_amount',
        'balance',
        'status',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'amount' => 'decimal:2',
        'settled_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\Period::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\AuxItem::class, 'customer_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(ArSettlement::class);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
