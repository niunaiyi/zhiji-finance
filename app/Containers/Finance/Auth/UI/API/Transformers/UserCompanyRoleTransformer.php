<?php

namespace App\Containers\Finance\Auth\UI\API\Transformers;

use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Transformers\Transformer;

class UserCompanyRoleTransformer extends Transformer
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform(UserCompanyRole $userCompanyRole): array
    {
        return [
            'id' => $userCompanyRole->id,
            'user_id' => $userCompanyRole->user_id,
            'company_id' => $userCompanyRole->company_id,
            'role' => $userCompanyRole->role,
            'is_active' => $userCompanyRole->is_active,
            'created_at' => $userCompanyRole->created_at?->toIso8601String(),
        ];
    }
}
