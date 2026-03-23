<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;
use Illuminate\Validation\Rule;

class AttachAuxCategoryToAccountRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = ['account_id'];

    public function rules(): array
    {
        return [
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('company_id', app('current.company_id'))],
            'aux_category_id' => ['required', Rule::exists('aux_categories', 'id')->where('company_id', app('current.company_id'))],
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
