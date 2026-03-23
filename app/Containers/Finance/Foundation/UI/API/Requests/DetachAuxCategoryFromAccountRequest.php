<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class DetachAuxCategoryFromAccountRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = ['account_id', 'aux_category_id'];

    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'aux_category_id' => 'required|exists:aux_categories,id',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
