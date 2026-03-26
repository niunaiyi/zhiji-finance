<?php

namespace App\Containers\Finance\FixedAsset\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'asset_no',
        'name',
        'category',
        'purchase_date',
        'original_value',
        'accumulated_depreciation',
        'net_value',
        'depreciation_method',
        'useful_life_months',
        'residual_rate',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'original_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'net_value' => 'decimal:2',
        'residual_rate' => 'decimal:4',
        'useful_life_months' => 'integer',
    ];

    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class);
    }
}
