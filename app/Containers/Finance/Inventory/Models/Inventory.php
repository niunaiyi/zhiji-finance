<?php

namespace App\Containers\Finance\Inventory\Models;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use BelongsToCompany;

    protected $table = 'inventories';

    protected $fillable = [
        'company_id',
        'inventory_id',
        'warehouse_id',
        'qty',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(AuxItem::class, 'inventory_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(AuxItem::class, 'warehouse_id');
    }
}
