<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class UpdateAuxCategoryRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = ['id'];

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
