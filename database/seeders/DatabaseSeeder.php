<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            \App\Containers\Finance\Auth\Data\Seeders\CompanySeeder::class,
            \App\Containers\Finance\Foundation\Data\Seeders\AuxCategorySeeder::class,
            \App\Containers\Finance\Foundation\Data\Seeders\AccountSeeder::class,
            \App\Containers\Finance\Foundation\Data\Seeders\PeriodSeeder::class,
        ]);
    }
}
