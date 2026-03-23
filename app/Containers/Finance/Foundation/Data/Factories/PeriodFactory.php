<?php

namespace App\Containers\Finance\Foundation\Data\Factories;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Factories\Factory;

class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2020, 2030);
        $month = $this->faker->numberBetween(1, 12);
        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        return [
            'fiscal_year' => $year,
            'period_number' => $month,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'open',
        ];
    }
}
