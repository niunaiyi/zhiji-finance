<?php

namespace App\Containers\Finance\Auth\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class AssignRoleRequest extends Request
{
    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    protected array $decode = [];

    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,accountant,auditor,viewer',
        ];
    }

    public function authorize(): bool
    {
        return $this->check([
            'hasAccess',
        ]);
    }
}
