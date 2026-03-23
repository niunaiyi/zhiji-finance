<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuxItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'aux_category_id',
        'code',
        'name',
        'parent_id',
        'is_active',
        'extra',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'extra' => 'array',
    ];

    public function auxCategory(): BelongsTo
    {
        return $this->belongsTo(AuxCategory::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AuxItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AuxItem::class, 'parent_id');
    }
}
