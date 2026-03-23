<?php

namespace App\Containers\Finance\Foundation\Data\Factories;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Factories\Factory;

class AuxItemFactory extends Factory
{
    protected $model = AuxItem::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{6,10}'),
            'name' => $this->faker->words(3, true),
            'parent_id' => null,
            'is_active' => true,
            'extra' => null,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withExtra(array $extra): self
    {
        return $this->state(fn (array $attributes) => [
            'extra' => $extra,
        ]);
    }
}
