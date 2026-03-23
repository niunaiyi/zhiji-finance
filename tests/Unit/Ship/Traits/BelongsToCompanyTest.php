<?php

namespace Tests\Unit\Ship\Traits;

use App\Ship\Scopes\CompanyScope;
use App\Ship\Tests\ShipTestCase;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BelongsToCompanyTest extends ShipTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        parent::tearDown();
    }

    public function testTraitAppliesCompanyScope(): void
    {
        $model = new TestModel();

        // Check that CompanyScope is registered
        $scopes = $model->getGlobalScopes();

        $this->assertArrayHasKey(CompanyScope::class, $scopes);
        $this->assertInstanceOf(CompanyScope::class, $scopes[CompanyScope::class]);
    }

    public function testTraitAutoFillsCompanyIdOnCreate(): void
    {
        // Set current company ID in service container
        app()->instance('current.company_id', 123);

        $model = new TestModel();
        $model->name = 'Test';
        $model->save();

        $this->assertEquals(123, $model->company_id);
    }

    public function testTraitDoesNotOverrideExplicitCompanyId(): void
    {
        // Set current company ID in service container
        app()->instance('current.company_id', 123);

        $model = new TestModel();
        $model->company_id = 456;
        $model->name = 'Test';
        $model->save();

        // Should keep the explicitly set company_id
        $this->assertEquals(456, $model->company_id);
    }

    public function testTraitHandlesNullCompanyId(): void
    {
        // No company ID in service container
        app()->instance('current.company_id', null);

        $model = new TestModel();
        $model->name = 'Test';
        $model->save();

        // Should not set company_id if not available
        $this->assertNull($model->company_id);
    }
}

// Test model for testing the trait
class TestModel extends Model
{
    use BelongsToCompany;

    protected $table = 'test_models';
    protected $guarded = [];
}
