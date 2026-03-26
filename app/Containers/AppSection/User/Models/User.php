<?php

namespace App\Containers\AppSection\User\Models;

use App\Containers\AppSection\Authorization\Enums\Role as RoleEnum;
use App\Containers\AppSection\User\Data\Collections\UserCollection;
use App\Containers\AppSection\User\Enums\Gender;
use App\Ship\Parents\Models\UserModel as ParentUserModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class User extends ParentUserModel
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'birth',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'immutable_datetime',
        'password' => 'hashed',
        'gender' => Gender::class,
        'birth' => 'immutable_date',
        'is_super_admin' => 'boolean',
    ];

    public function newCollection(array $models = []): UserCollection
    {
        return new UserCollection($models);
    }

    /**
     * Allows Passport to find the user by email (case-insensitive).
     */
    public function findForPassport(string $username): self|null
    {
        return self::orWhereRaw('lower(email) = lower(?)', [$username])->first();
    }

    public function isSuperAdmin(): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach (array_keys(config('auth.guards')) as $guard) {
            if ($this->hasRole(RoleEnum::SUPER_ADMIN, $guard)) {
                return true;
            }
        }

        return false;
    }

    protected function email(): Attribute
    {
        return new Attribute(
            get: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
        );
    }

    public function companyRoles(): HasMany
    {
        return $this->hasMany(\App\Containers\Finance\Auth\Models\UserCompanyRole::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Containers\Finance\Auth\Models\Company::class, 'user_company_roles')
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }
}
