<?php

namespace App\Containers\Finance\AccountsPayable\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApSettlement extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'ap_bill_id',
        'ap_payment_id',
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
        return $this->belongsTo(ApBill::class, 'ap_bill_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(ApPayment::class, 'ap_payment_id');
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(\App\Containers\AppSection\User\Models\User::class, 'settled_by');
    }
}
