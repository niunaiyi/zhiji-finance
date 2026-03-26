<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class ListPeriodsRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'fiscal_year' => 'nullable|integer|min:2000|max:2100',
            'status' => 'nullable|in:open,closed,locked',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
