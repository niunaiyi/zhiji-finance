<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAuxItemRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = ['id'];

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('aux_items', 'id')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id);
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
