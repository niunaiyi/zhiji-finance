<?php

namespace Tests\Unit\Containers\Finance\Auth\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Database\QueryException;

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

    public function testCompanyCodeMustBeUnique(): void
    {
        Company::factory()->create(['code' => 'DUPLICATE']);

        $this->expectException(QueryException::class);
        Company::factory()->create(['code' => 'DUPLICATE']);
    }

    public function testCompanyStatusMustBeValidEnum(): void
    {
        $this->expectException(QueryException::class);
        Company::factory()->create(['status' => 'invalid_status']);
    }

    public function testCompanyHasCorrectDefaults(): void
    {
        $company = Company::factory()->create([
            'code' => 'DEFAULT01',
            'name' => 'Default Test Company',
        ]);

        $this->assertEquals(1, $company->fiscal_year_start);
        $this->assertEquals('active', $company->status);
    }

    public function testFiscalYearStartAcceptsValidRange(): void
    {
        $company = Company::factory()->create([
            'code' => 'VALID01',
            'fiscal_year_start' => 6,
        ]);

        $this->assertEquals(6, $company->fiscal_year_start);

        $company2 = Company::factory()->create([
            'code' => 'VALID02',
            'fiscal_year_start' => 1,
        ]);

        $this->assertEquals(1, $company2->fiscal_year_start);

        $company3 = Company::factory()->create([
            'code' => 'VALID03',
            'fiscal_year_start' => 12,
        ]);

        $this->assertEquals(12, $company3->fiscal_year_start);
    }

    public function testFiscalYearStartRejectsBelowMinimum(): void
    {
        $this->expectException(QueryException::class);
        Company::factory()->create([
            'code' => 'INVALID01',
            'fiscal_year_start' => 0,
        ]);
    }

    public function testFiscalYearStartRejectsAboveMaximum(): void
    {
        $this->expectException(QueryException::class);
        Company::factory()->create([
            'code' => 'INVALID02',
            'fiscal_year_start' => 13,
        ]);
    }

    public function testCompanyStatusCanBeSuspended(): void
    {
        $company = Company::factory()->create([
            'code' => 'SUSPENDED01',
            'status' => 'suspended',
        ]);

        $this->assertEquals('suspended', $company->status);
    }
}
