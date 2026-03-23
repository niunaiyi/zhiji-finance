<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class ListAuxItemsRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'aux_category_id' => 'nullable|integer|exists:aux_categories,id',
            'is_active' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
