<?php

namespace App\Containers\Finance\Auth\Models;

use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $fillable = [
        'code',
        'name',
        'fiscal_year_start',
        'status',
        'identifier',
    ];

    protected $casts = [
        'fiscal_year_start' => 'integer',
        'status' => 'string',
    ];

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_company_roles')
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }
}
