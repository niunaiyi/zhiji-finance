<?php

namespace App\Containers\Finance\AccountsReceivable\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArSettlement extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'ar_bill_id',
        'ar_receipt_id',
        'amount',
        'settled_at',
        'settled_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(ArBill::class, 'ar_bill_id');
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(ArReceipt::class, 'ar_receipt_id');
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\AppSection\User\Models\User::class, 'settled_by');
    }
}
