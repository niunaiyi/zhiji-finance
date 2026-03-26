<?php

namespace App\Containers\Finance\Purchase\Models;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

/**
 * 采购订单模型。
 * 记录采购业务的核心数据，如供应商、单据日期、总额、状态等。
 */
class PurchaseOrder extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'order_no',
        'order_date',
        'supplier_id',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(AuxItem::class, 'supplier_id');
    }
}
