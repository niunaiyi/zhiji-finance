<?php

namespace App\Containers\Finance\Purchase\Models;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

class PurchaseReceipt extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'receipt_no',
        'receipt_date',
        'supplier_id',
        'status',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(AuxItem::class, 'supplier_id');
    }
}
