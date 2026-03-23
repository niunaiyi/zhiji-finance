<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateAuxItemRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'aux_category_id' => [
                'required',
                'integer',
                Rule::exists('aux_categories', 'id')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id);
                }),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('aux_items')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id)
                                 ->where('aux_category_id', $this->input('aux_category_id'));
                }),
            ],
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('aux_items', 'id')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id)
                                 ->where('aux_category_id', $this->input('aux_category_id'));
                }),
            ],
            'is_active' => 'boolean',
            'extra' => 'nullable|array',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
