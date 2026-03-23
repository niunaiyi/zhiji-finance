<?php

namespace App\Containers\Finance\Auth\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class CreateCompanyRequest extends Request
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
            'code' => 'required|string|max:20|unique:companies,code|alpha_num',
            'name' => 'required|string|max:100',
            'fiscal_year_start' => 'required|integer|min:1|max:12',
        ];
    }

    public function authorize(): bool
    {
        return $this->check([
            'hasAccess',
        ]);
    }
}
