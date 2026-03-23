<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class DeactivateAccountRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = ['id'];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
