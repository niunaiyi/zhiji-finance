<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class CreateAccountRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:accounts,id',
            'element_type' => 'nullable|in:asset,liability,equity,income,expense,cost',
            'balance_direction' => 'nullable|in:debit,credit',
            'has_aux' => 'nullable|boolean',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
