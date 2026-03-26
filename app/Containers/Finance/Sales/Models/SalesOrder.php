<?php

namespace App\Containers\Finance\Sales\Models;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

/**
 * 销售订单模型。
 * 记录销售业务核心数据，关联客户及其订单状态。
 */
class SalesOrder extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'order_no',
        'order_date',
        'customer_id',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(AuxItem::class, 'customer_id');
    }
}
