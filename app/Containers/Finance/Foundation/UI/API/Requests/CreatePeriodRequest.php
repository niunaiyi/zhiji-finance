<?php

namespace App\Containers\Finance\Foundation\UI\API\Requests;

use App\Ship\Parents\Requests\Request;

class CreatePeriodRequest extends Request
{
    protected array $access = ['permissions' => '', 'roles' => ''];
    protected array $decode = [];
    protected array $urlParameters = [];

    public function rules(): array
    {
        return [
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'period_number' => 'required|integer|between:1,12',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|in:open,closed,locked',
        ];
    }

    public function authorize(): bool
    {
        return $this->check(['hasAccess']);
    }
}
