<?php

namespace Tests\Unit\Containers\Finance\Auth\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Tests\TestCase;

class CompanyTest extends TestCase
{
    public function testCompanyCanBeCreated(): void
    {
        $company = Company::factory()->create([
            'code' => 'TEST01',
            'name' => 'Test Company',
        ]);

        $this->assertDatabaseHas('companies', [
            'code' => 'TEST01',
            'name' => 'Test Company',
        ]);
    }
}
