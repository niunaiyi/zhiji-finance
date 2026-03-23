<?php

namespace App\Containers\Finance\Auth\Data\Factories;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{6}'),
            'name' => $this->faker->company(),
            'fiscal_year_start' => 1,
            'status' => 'active',
        ];
    }
}
