<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class CreateAuxCategoryRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:50',
            'is_system' => 'nullable|boolean',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
