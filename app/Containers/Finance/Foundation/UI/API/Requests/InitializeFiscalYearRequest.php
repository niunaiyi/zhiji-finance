<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class InitializeFiscalYearRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'fiscal_year' => 'required|integer|min:2000|max:2100',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
