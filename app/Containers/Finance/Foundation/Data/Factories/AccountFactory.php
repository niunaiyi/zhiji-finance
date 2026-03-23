<?php

namespace App\Containers\Finance\Foundation\Data\Factories;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'name' => $this->faker->words(3, true),
            'parent_id' => null,
            'level' => 1,
            'element_type' => $this->faker->randomElement(['asset', 'liability', 'equity', 'income', 'expense', 'cost']),
            'balance_direction' => $this->faker->randomElement(['debit', 'credit']),
            'is_detail' => true,
            'is_active' => true,
            'has_aux' => false,
        ];
    }
}
