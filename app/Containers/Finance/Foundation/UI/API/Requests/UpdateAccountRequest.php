<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class UpdateAccountRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = ['id'];

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
