<?php

namespace App\Containers\Finance\Voucher\Models;

use App\Ship\Parents\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherLineAux extends Model
{
    protected $table = 'voucher_line_aux';

    protected $fillable = [
        'voucher_line_id',
        'aux_category_id',
        'aux_item_id',
    ];

    public function line(): BelongsTo
    {
        return $this->belongsTo(VoucherLine::class, 'voucher_line_id');
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
