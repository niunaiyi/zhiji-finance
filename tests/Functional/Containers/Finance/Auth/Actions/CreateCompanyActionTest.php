<?php

namespace Tests\Functional\Containers\Finance\Auth\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Actions\CreateCompanyAction;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class CreateCompanyActionTest extends TestCase
{
    public function testCreateCompanyAssignsUserAsAdmin(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'code' => 'TEST01',
            'name' => 'Test Company',
            'fiscal_year_start' => 1,
        ];

        // Act
        $action = app(CreateCompanyAction::class);
        $company = $action->run($data);

        // Assert
        $this->assertDatabaseHas('companies', [
            'code' => 'TEST01',
            'name' => 'Test Company',
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
