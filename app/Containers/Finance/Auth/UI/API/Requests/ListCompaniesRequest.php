<?php

namespace App\Containers\Finance\Auth\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class ListCompaniesRequest extends Request
{
    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    protected array $decode = [];

    protected array $urlParameters = [];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->check([
            'hasAccess',
        ]);
    }
}
