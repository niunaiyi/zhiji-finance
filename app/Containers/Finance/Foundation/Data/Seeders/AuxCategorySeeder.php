<?php

namespace App\Containers\Finance\Foundation\Data\Seeders;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Seeders\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds system auxiliary categories for the default company.
 *
 * Creates 6 built-in auxiliary categories (customer, supplier, dept, employee, inventory, project)
 * that are marked as system categories (is_system=true) and cannot be modified or deleted.
 *
 * Depends on: CompanySeeder (requires DEFAULT company to exist)
 */
class AuxCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $company = Company::where('code', 'DEFAULT')->first();

            if (!$company) {
                return;
            }

            $categories = [
                ['code' => 'customer', 'name' => '客户', 'is_system' => true],
                ['code' => 'supplier', 'name' => '供应商', 'is_system' => true],
                ['code' => 'dept', 'name' => '部门', 'is_system' => true],
                ['code' => 'employee', 'name' => '职员', 'is_system' => true],
                ['code' => 'inventory', 'name' => '存货', 'is_system' => true],
                ['code' => 'project', 'name' => '项目', 'is_system' => true],
            ];

            foreach ($categories as $category) {
                AuxCategory::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => $category['code'],
                    ],
                    [
                        'name' => $category['name'],
                        'is_system' => $category['is_system'],
                    ]
                );
            }
        });
    }
}
