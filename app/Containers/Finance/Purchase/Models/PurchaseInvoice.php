<?php

namespace App\Containers\Finance\Purchase\Models;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

class PurchaseInvoice extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'invoice_no',
        'invoice_date',
        'supplier_id',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(AuxItem::class, 'supplier_id');
    }
}
