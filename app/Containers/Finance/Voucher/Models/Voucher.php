<?php

namespace App\Containers\Finance\Voucher\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'period_id',
        'voucher_type',
        'voucher_no',
        'voucher_date',
        'status',
        'summary',
        'total_debit',
        'total_credit',
        'source_type',
        'source_id',
        'created_by',
        'reviewed_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'posted_at' => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(VoucherLine::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\Period::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\AppSection\User\Models\User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\AppSection\User\Models\User::class, 'reviewed_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\AppSection\User\Models\User::class, 'posted_by');
    }

    public function scopeByPeriod($query, int $periodId)
    {
        return $query->where('period_id', $periodId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('voucher_type', $type);
    }
}
