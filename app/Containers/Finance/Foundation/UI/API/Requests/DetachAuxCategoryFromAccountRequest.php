<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;
use Illuminate\Validation\Rule;

class DetachAuxCategoryFromAccountRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = ['account_id', 'aux_category_id'];

    public function rules(): array
    {
        return [
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('company_id', app('current.company_id'))],
            'aux_category_id' => ['required', Rule::exists('aux_categories', 'id')->where('company_id', app('current.company_id'))],
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
