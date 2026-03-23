<?php

namespace App\Containers\Finance\GeneralLedger\Models;

use App\Ship\Parents\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceAux extends Model
{
    protected $table = 'balance_aux';

    protected $fillable = [
        'company_id',
        'period_id',
        'account_id',
        'aux_category_id',
        'aux_item_id',
        'opening_debit',
        'opening_credit',
        'period_debit',
        'period_credit',
        'closing_debit',
        'closing_credit',
    ];

    protected $casts = [
        'opening_debit' => 'decimal:2',
        'opening_credit' => 'decimal:2',
        'period_debit' => 'decimal:2',
        'period_credit' => 'decimal:2',
        'closing_debit' => 'decimal:2',
        'closing_credit' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\Period::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\AuxCategory::class, 'aux_category_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\Finance\Foundation\Models\AuxItem::class, 'aux_item_id');
    }
}
