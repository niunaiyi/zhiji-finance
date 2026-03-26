<?php

namespace App\Containers\Finance\Sales\Models;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

class SalesShipment extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'shipment_no',
        'shipment_date',
        'customer_id',
        'status',
    ];

    protected $casts = [
        'shipment_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(AuxItem::class, 'customer_id');
    }
}
