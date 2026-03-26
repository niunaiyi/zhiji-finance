<?php

namespace App\Containers\Finance\Voucher\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherLine extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'voucher_id',
        'line_no',
        'account_id',
        'summary',
        'debit',
        'credit',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\Account::class);
    }

    public function auxItems(): HasMany
    {
        return $this->hasMany(VoucherLineAux::class);
    }
}
