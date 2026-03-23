<?php

namespace App\Ship\Traits;

use App\Ship\Scopes\CompanyScope;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        // Add CompanyScope to automatically filter queries by company_id
        static::addGlobalScope(new CompanyScope());

        // Auto-fill company_id on model creation
        static::creating(function ($model) {
            if ($model->company_id === null) {
                // Check if binding exists before resolving
                if (app()->bound('current.company_id')) {
                    $companyId = app('current.company_id');
                    if ($companyId) {
                        $model->company_id = $companyId;
                    }
                }
            }
        });
    }
}
