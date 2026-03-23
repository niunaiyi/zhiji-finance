<?php

namespace App\Ship\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = app('current.company_id');

        if ($companyId) {
            $builder->where('company_id', $companyId);
        }
    }
}
