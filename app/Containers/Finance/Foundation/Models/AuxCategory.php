<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AuxCategory extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'code',
        'name',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_aux_categories')
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps();
    }
}
