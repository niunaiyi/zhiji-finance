<?php

namespace Tests\Unit\Ship\Scopes;

use App\Ship\Tests\ShipTestCase;
use App\Ship\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery;

class CompanyScopeTest extends ShipTestCase
{
    public function testApplyScopeAddsCompanyIdWhere(): void
    {
        // Arrange
        app()->instance('current.company_id', 5);
        $scope = new CompanyScope();
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        // Expect
        $builder->shouldReceive('where')
            ->once()
            ->with('company_id', 5)
            ->andReturnSelf();

        // Act
        $scope->apply($builder, $model);

        // Assert - handled by Mockery expectations
        $this->assertTrue(true);
    }
}
