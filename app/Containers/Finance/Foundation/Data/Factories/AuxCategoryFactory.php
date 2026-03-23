<?php

namespace App\Containers\Finance\Foundation\Data\Factories;

use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Factories\Factory;

class AuxCategoryFactory extends Factory
{
    protected $model = AuxCategory::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z]{4,8}'),
            'name' => $this->faker->words(2, true),
            'is_system' => false,
        ];
    }

    public function system(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }
}
