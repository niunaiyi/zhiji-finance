# P0 Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build multi-tenant infrastructure, company management, and complete Chart of Accounts system with auxiliary accounting and fiscal period management.

**Architecture:** Porto SAP architecture with Finance Section containing Auth and Foundation containers. Multi-tenant isolation via header-based company selection with GlobalScope auto-filtering. All business models use BelongsToCompany trait for automatic company_id scoping.

**Tech Stack:** Apiato v13 (Laravel 11), PostgreSQL 15, Laravel Passport (OAuth2), Porto architecture

---

## File Structure Overview

### Ship Layer (Shared Infrastructure)
```
app/Ship/
├── Traits/BelongsToCompany.php          [CREATE] - Auto-fill company_id on models
├── Scopes/CompanyScope.php              [CREATE] - Auto-filter queries by company_id
└── Middleware/SwitchTenantMiddleware.php [CREATE] - Validate X-Company-Id header
```

### Finance/Auth Container
```
app/Containers/Finance/Auth/
├── Models/
│   ├── Company.php                      [CREATE]
│   └── UserCompanyRole.php              [CREATE]
├── Data/
│   ├── Migrations/
│   │   ├── *_create_companies_table.php [CREATE]
│   │   └── *_create_user_company_roles_table.php [CREATE]
│   ├── Repositories/
│   │   ├── CompanyRepository.php        [CREATE]
│   │   └── UserCompanyRoleRepository.php [CREATE]
│   ├── Factories/
│   │   ├── CompanyFactory.php           [CREATE]
│   │   └── UserCompanyRoleFactory.php   [CREATE]
│   └── Seeders/
│       └── CompanySeeder.php            [CREATE]
├── Actions/
│   ├── CreateCompanyAction.php          [CREATE]
│   ├── ListUserCompaniesAction.php      [CREATE]
│   └── AssignUserRoleAction.php         [CREATE]
├── Tasks/
│   ├── CreateCompanyTask.php            [CREATE]
│   ├── AssignUserRoleTask.php           [CREATE]
│   ├── FindUserCompaniesTask.php        [CREATE]
│   └── ValidateUserCompanyAccessTask.php [CREATE]
├── UI/API/
│   ├── Controllers/
│   │   ├── CreateCompanyController.php  [CREATE]
│   │   ├── ListCompaniesController.php  [CREATE]
│   │   └── AssignRoleController.php     [CREATE]
│   ├── Requests/
│   │   ├── CreateCompanyRequest.php     [CREATE]
│   │   ├── ListCompaniesRequest.php     [CREATE]
│   │   └── AssignRoleRequest.php        [CREATE]
│   ├── Transformers/
│   │   ├── CompanyTransformer.php       [CREATE]
│   │   └── UserCompanyRoleTransformer.php [CREATE]
│   └── Routes/
│       ├── CreateCompany.v1.private.php [CREATE]
│       ├── ListCompanies.v1.private.php [CREATE]
│       └── AssignRole.v1.private.php    [CREATE]
└── Tests/
    └── Functional/
        ├── CreateCompanyTest.php        [CREATE]
        ├── ListCompaniesTest.php        [CREATE]
        └── AssignRoleTest.php           [CREATE]
```

### Finance/Foundation Container
```
app/Containers/Finance/Foundation/
├── Models/
│   ├── Account.php                      [CREATE]
│   ├── AuxCategory.php                  [CREATE]
│   ├── AuxItem.php                      [CREATE]
│   ├── AccountAuxCategory.php           [CREATE]
│   └── Period.php                       [CREATE]
├── Data/
│   ├── Migrations/
│   │   ├── *_create_accounts_table.php  [CREATE]
│   │   ├── *_create_aux_categories_table.php [CREATE]
│   │   ├── *_create_aux_items_table.php [CREATE]
│   │   ├── *_create_account_aux_categories_table.php [CREATE]
│   │   └── *_create_periods_table.php   [CREATE]
│   ├── Repositories/
│   │   ├── AccountRepository.php        [CREATE]
│   │   ├── AuxCategoryRepository.php    [CREATE]
│   │   ├── AuxItemRepository.php        [CREATE]
│   │   ├── AccountAuxCategoryRepository.php [CREATE]
│   │   └── PeriodRepository.php         [CREATE]
│   ├── Factories/
│   │   ├── AccountFactory.php           [CREATE]
│   │   ├── AuxCategoryFactory.php       [CREATE]
│   │   ├── AuxItemFactory.php           [CREATE]
│   │   └── PeriodFactory.php            [CREATE]
│   └── Seeders/
│       ├── AuxCategorySeeder.php        [CREATE]
│       ├── AccountSeeder.php            [CREATE]
│       └── PeriodSeeder.php             [CREATE]
├── Actions/ (15+ action files)
├── Tasks/ (20+ task files)
├── UI/API/
│   ├── Controllers/ (15+ controller files)
│   ├── Requests/ (15+ request files)
│   ├── Transformers/ (5 transformer files)
│   └── Routes/ (15+ route files)
└── Tests/
    └── Functional/ (15+ test files)
```

---

## Implementation Phases

Due to the large scope, this plan is organized into 6 phases:

1. **Phase 1: Ship Layer & Database Setup** - Multi-tenant infrastructure
2. **Phase 2: Finance/Auth Container** - Company management
3. **Phase 3: Finance/Foundation - Accounts** - Chart of Accounts
4. **Phase 4: Finance/Foundation - Aux Categories** - Auxiliary accounting
5. **Phase 5: Finance/Foundation - Periods** - Fiscal period management
6. **Phase 6: Seeders & Integration** - Default data and end-to-end tests

---

## PHASE 1: Ship Layer & Database Setup

### Task 1.1: Create CompanyScope GlobalScope

**Files:**
- Create: `app/Ship/Scopes/CompanyScope.php`
- Test: `tests/Unit/Ship/Scopes/CompanyScopeTest.php`

- [ ] **Step 1: Write failing test for CompanyScope**

```php
<?php

namespace Tests\Unit\Ship\Scopes;

use App\Ship\Parents\Tests\PhpUnit\TestCase;
use App\Ship\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery;

class CompanyScopeTest extends TestCase
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Ship/Scopes/CompanyScopeTest.php`
Expected: FAIL with "Class 'App\Ship\Scopes\CompanyScope' not found"

- [ ] **Step 3: Create CompanyScope implementation**

```php
<?php

namespace App\Ship\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = app('current.company_id');

        if ($companyId) {
            $builder->where('company_id', $companyId);
        }
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Ship/Scopes/CompanyScopeTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Ship/Scopes/CompanyScope.php tests/Unit/Ship/Scopes/CompanyScopeTest.php
git commit -m "feat(ship): add CompanyScope for multi-tenant query filtering"
```

---

### Task 1.2: Create BelongsToCompany Trait

**Files:**
- Create: `app/Ship/Traits/BelongsToCompany.php`
- Test: `tests/Unit/Ship/Traits/BelongsToCompanyTest.php`

- [ ] **Step 1: Write failing test for BelongsToCompany**

```php
<?php

namespace Tests\Unit\Ship\Traits;

use App\Ship\Parents\Tests\PhpUnit\TestCase;
use App\Ship\Scopes\CompanyScope;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class BelongsToCompanyTest extends TestCase
{
    public function testTraitAddsCompanyScopeToModel(): void
    {
        // Arrange
        $model = new class extends Model {
            use BelongsToCompany;
        };

        // Act
        $scopes = $model->getGlobalScopes();

        // Assert
        $this->assertArrayHasKey(CompanyScope::class, $scopes);
    }

    public function testTraitAutoFillsCompanyIdOnCreating(): void
    {
        // Arrange
        app()->instance('current.company_id', 10);
        $model = new class extends Model {
            use BelongsToCompany;
            protected $fillable = ['name'];
        };

        // Act
        $model->name = 'Test';
        $model->fireModelEvent('creating');

        // Assert
        $this->assertEquals(10, $model->company_id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Ship/Traits/BelongsToCompanyTest.php`
Expected: FAIL with "Trait 'App\Ship\Traits\BelongsToCompany' not found"

- [ ] **Step 3: Create BelongsToCompany implementation**

```php
<?php

namespace App\Ship\Traits;

use App\Ship\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function (Model $model) {
            if (!isset($model->company_id)) {
                $model->company_id = app('current.company_id');
            }
        });
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Ship/Traits/BelongsToCompanyTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Ship/Traits/BelongsToCompany.php tests/Unit/Ship/Traits/BelongsToCompanyTest.php
git commit -m "feat(ship): add BelongsToCompany trait for auto company_id handling"
```

---

### Task 1.3: Create SwitchTenantMiddleware

**Files:**
- Create: `app/Ship/Middleware/SwitchTenantMiddleware.php`
- Test: `tests/Unit/Ship/Middleware/SwitchTenantMiddlewareTest.php`

- [ ] **Step 1: Write failing test for SwitchTenantMiddleware**

```php
<?php

namespace Tests\Unit\Ship\Middleware;

use App\Ship\Middleware\SwitchTenantMiddleware;
use App\Ship\Parents\Tests\PhpUnit\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SwitchTenantMiddlewareTest extends TestCase
{
    public function testMiddlewareRejectsRequestWithoutCompanyIdHeader(): void
    {
        // Arrange
        $middleware = new SwitchTenantMiddleware();
        $request = Request::create('/api/v1/accounts', 'GET');
        $this->actingAs($this->getTestingUser());

        // Expect exception
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('X-Company-Id header is required');

        // Act
        $middleware->handle($request, function () {});
    }

    public function testMiddlewareRejectsUnauthorizedCompanyAccess(): void
    {
        // Arrange
        $middleware = new SwitchTenantMiddleware();
        $request = Request::create('/api/v1/accounts', 'GET');
        $request->headers->set('X-Company-Id', '999');
        $this->actingAs($this->getTestingUser());

        // Expect exception
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied to company');

        // Act
        $middleware->handle($request, function () {});
    }

    public function testMiddlewareBindsCompanyIdToContainer(): void
    {
        // Arrange
        $middleware = new SwitchTenantMiddleware();
        $request = Request::create('/api/v1/accounts', 'GET');
        $request->headers->set('X-Company-Id', '1');
        $user = $this->getTestingUser();
        $this->actingAs($user);

        // Mock user_company_roles check
        DB::shouldReceive('table')
            ->with('user_company_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', $user->id)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('company_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('is_active', true)
            ->andReturnSelf();
        DB::shouldReceive('first')
            ->andReturn((object)['role' => 'admin']);

        // Act
        $middleware->handle($request, function () {});

        // Assert
        $this->assertEquals(1, app('current.company_id'));
        $this->assertEquals('admin', app('current.role'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Ship/Middleware/SwitchTenantMiddlewareTest.php`
Expected: FAIL with "Class 'App\Ship\Middleware\SwitchTenantMiddleware' not found"

- [ ] **Step 3: Create SwitchTenantMiddleware implementation**

```php
<?php

namespace App\Ship\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SwitchTenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header('X-Company-Id');

        if (!$companyId) {
            throw new HttpException(400, 'X-Company-Id header is required');
        }

        $user = $request->user();

        if (!$user) {
            throw new HttpException(401, 'Unauthenticated');
        }

        $userCompanyRole = DB::table('user_company_roles')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (!$userCompanyRole) {
            throw new HttpException(403, 'Access denied to company');
        }

        app()->instance('current.company_id', (int)$companyId);
        app()->instance('current.role', $userCompanyRole->role);

        return $next($request);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Ship/Middleware/SwitchTenantMiddlewareTest.php`
Expected: PASS

- [ ] **Step 5: Register middleware in HTTP Kernel**

Modify: `app/Ship/Kernels/HttpKernel.php`

Add to `$middlewareAliases`:
```php
'tenant' => \App\Ship\Middleware\SwitchTenantMiddleware::class,
```

- [ ] **Step 6: Commit**

```bash
git add app/Ship/Middleware/SwitchTenantMiddleware.php tests/Unit/Ship/Middleware/SwitchTenantMiddlewareTest.php app/Ship/Kernels/HttpKernel.php
git commit -m "feat(ship): add SwitchTenantMiddleware for company access validation"
```

---

## PHASE 2: Finance/Auth Container

### Task 2.1: Create Finance Section and Auth Container Structure

- [ ] **Step 1: Create Finance/Auth container directories**

```bash
mkdir -p app/Containers/Finance/Auth/{Models,Data/{Migrations,Repositories,Factories,Seeders},Actions,Tasks,UI/API/{Controllers,Requests,Transformers,Routes},Tests/Functional}
```

- [ ] **Step 2: Create composer.json for Finance/Auth container**

Create: `app/Containers/Finance/Auth/composer.json`

```json
{
    "name": "finance/auth",
    "description": "Finance Auth Container",
    "type": "container",
    "require": {}
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Containers/Finance/Auth/
git commit -m "feat(auth): create Finance/Auth container structure"
```

---

### Task 2.2: Create Company Model and Migration

**Files:**
- Create: `app/Containers/Finance/Auth/Models/Company.php`
- Create: `app/Containers/Finance/Auth/Data/Migrations/*_create_companies_table.php`
- Create: `app/Containers/Finance/Auth/Data/Factories/CompanyFactory.php`

- [ ] **Step 1: Create companies migration**

```bash
php artisan make:migration create_companies_table --path=app/Containers/Finance/Auth/Data/Migrations
```

Edit the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->tinyInteger('fiscal_year_start')->default(1);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

Expected: Migration successful

- [ ] **Step 3: Create Company model**

```php
<?php

namespace App\Containers\Finance\Auth\Models;

use App\Ship\Parents\Models\Model;

class Company extends Model
{
    protected $fillable = [
        'code',
        'name',
        'fiscal_year_start',
        'status',
    ];

    protected $casts = [
        'fiscal_year_start' => 'integer',
    ];
}
```

- [ ] **Step 4: Create CompanyFactory**

```php
<?php

namespace App\Containers\Finance\Auth\Data\Factories;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{6}'),
            'name' => $this->faker->company(),
            'fiscal_year_start' => 1,
            'status' => 'active',
        ];
    }
}
```

- [ ] **Step 5: Write test for Company model**

Create: `tests/Unit/Containers/Finance/Auth/Models/CompanyTest.php`

```php
<?php

namespace Tests\Unit\Containers\Finance\Auth\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Tests\PhpUnit\TestCase;

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
```

- [ ] **Step 6: Run test**

```bash
php artisan test tests/Unit/Containers/Finance/Auth/Models/CompanyTest.php
```

Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Containers/Finance/Auth/Models/Company.php app/Containers/Finance/Auth/Data/Migrations/ app/Containers/Finance/Auth/Data/Factories/CompanyFactory.php tests/Unit/Containers/Finance/Auth/Models/CompanyTest.php
git commit -m "feat(auth): add Company model and migration"
```

---

### Task 2.3: Create UserCompanyRole Model and Migration

**Files:**
- Create: `app/Containers/Finance/Auth/Models/UserCompanyRole.php`
- Create: `app/Containers/Finance/Auth/Data/Migrations/*_create_user_company_roles_table.php`
- Create: `app/Containers/Finance/Auth/Data/Factories/UserCompanyRoleFactory.php`

- [ ] **Step 1: Create user_company_roles migration**

```bash
php artisan make:migration create_user_company_roles_table --path=app/Containers/Finance/Auth/Data/Migrations
```

Edit the migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_company_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('company_id')->constrained()->onDelete('restrict');
            $table->enum('role', ['admin', 'accountant', 'auditor', 'viewer']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_company_roles');
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

Expected: Migration successful

- [ ] **Step 3: Create UserCompanyRole model**

```php
<?php

namespace App\Containers\Finance\Auth\Models;

use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCompanyRole extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

- [ ] **Step 4: Create UserCompanyRoleFactory**

```php
<?php

namespace App\Containers\Finance\Auth\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Factories\Factory;

class UserCompanyRoleFactory extends Factory
{
    protected $model = UserCompanyRole::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'role' => 'accountant',
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 5: Write test**

Create: `tests/Unit/Containers/Finance/Auth/Models/UserCompanyRoleTest.php`

```php
<?php

namespace Tests\Unit\Containers\Finance\Auth\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Tests\PhpUnit\TestCase;

class UserCompanyRoleTest extends TestCase
{
    public function testUserCompanyRoleCanBeCreated(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $role = UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);
    }
}
```

- [ ] **Step 6: Run test**

```bash
php artisan test tests/Unit/Containers/Finance/Auth/Models/UserCompanyRoleTest.php
```

Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Containers/Finance/Auth/Models/UserCompanyRole.php app/Containers/Finance/Auth/Data/Migrations/ app/Containers/Finance/Auth/Data/Factories/UserCompanyRoleFactory.php tests/Unit/Containers/Finance/Auth/Models/UserCompanyRoleTest.php
git commit -m "feat(auth): add UserCompanyRole model and migration"
```

---

### Task 2.4: Create CompanyRepository

**Files:**
- Create: `app/Containers/Finance/Auth/Data/Repositories/CompanyRepository.php`

- [ ] **Step 1: Create CompanyRepository**

```php
<?php

namespace App\Containers\Finance\Auth\Data\Repositories;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Repositories\Repository;

class CompanyRepository extends Repository
{
    protected $fieldSearchable = [
        'code' => 'like',
        'name' => 'like',
        'status' => '=',
    ];

    public function model(): string
    {
        return Company::class;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Containers/Finance/Auth/Data/Repositories/CompanyRepository.php
git commit -m "feat(auth): add CompanyRepository"
```

---

### Task 2.5: Create CreateCompanyAction with TDD

**Files:**
- Create: `app/Containers/Finance/Auth/Actions/CreateCompanyAction.php`
- Create: `app/Containers/Finance/Auth/Tasks/CreateCompanyTask.php`
- Create: `app/Containers/Finance/Auth/Tasks/AssignUserRoleTask.php`
- Test: `tests/Functional/Containers/Finance/Auth/Actions/CreateCompanyActionTest.php`

- [ ] **Step 1: Write failing test for CreateCompanyAction**

```php
<?php

namespace Tests\Functional\Containers\Finance\Auth\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Actions\CreateCompanyAction;
use App\Ship\Parents\Tests\PhpUnit\TestCase;
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
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Functional/Containers/Finance/Auth/Actions/CreateCompanyActionTest.php
```

Expected: FAIL with "Class 'CreateCompanyAction' not found"

- [ ] **Step 3: Create CreateCompanyTask**

```php
<?php

namespace App\Containers\Finance\Auth\Tasks;

use App\Containers\Finance\Auth\Data\Repositories\CompanyRepository;
use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Tasks\Task;

class CreateCompanyTask extends Task
{
    public function __construct(
        private readonly CompanyRepository $repository
    ) {}

    public function run(array $data): Company
    {
        return $this->repository->create($data);
    }
}
```

- [ ] **Step 4: Create AssignUserRoleTask**

```php
<?php

namespace App\Containers\Finance\Auth\Tasks;

use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Tasks\Task;

class AssignUserRoleTask extends Task
{
    public function run(int $userId, int $companyId, string $role): UserCompanyRole
    {
        return UserCompanyRole::updateOrCreate(
            [
                'user_id' => $userId,
                'company_id' => $companyId,
            ],
            [
                'role' => $role,
                'is_active' => true,
            ]
        );
    }
}
```

- [ ] **Step 5: Create CreateCompanyAction**

```php
<?php

namespace App\Containers\Finance\Auth\Actions;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Tasks\AssignUserRoleTask;
use App\Containers\Finance\Auth\Tasks\CreateCompanyTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class CreateCompanyAction extends Action
{
    public function __construct(
        private readonly CreateCompanyTask $createCompanyTask,
        private readonly AssignUserRoleTask $assignUserRoleTask,
    ) {}

    public function run(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $company = $this->createCompanyTask->run($data);

            $this->assignUserRoleTask->run(
                auth()->id(),
                $company->id,
                'admin'
            );

            return $company;
        });
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

```bash
php artisan test tests/Functional/Containers/Finance/Auth/Actions/CreateCompanyActionTest.php
```

Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Containers/Finance/Auth/Actions/CreateCompanyAction.php app/Containers/Finance/Auth/Tasks/ tests/Functional/Containers/Finance/Auth/Actions/CreateCompanyActionTest.php
git commit -m "feat(auth): add CreateCompanyAction with user role assignment"
```

---

### Task 2.6: Create ListUserCompaniesAction

**Files:**
- Create: `app/Containers/Finance/Auth/Actions/ListUserCompaniesAction.php`
- Create: `app/Containers/Finance/Auth/Tasks/FindUserCompaniesTask.php`

Following TDD pattern from Task 2.5:
- Write test expecting user's companies with eager-loaded relationships
- Implement FindUserCompaniesTask querying user_company_roles with Company relationship
- Implement ListUserCompaniesAction calling the task
- Verify test passes, commit

### Task 2.7: Create AssignUserRoleAction with Authorization

**Files:**
- Create: `app/Containers/Finance/Auth/Actions/AssignUserRoleAction.php`
- Create: `app/Containers/Finance/Auth/Tasks/ValidateUserCompanyAccessTask.php`

**Critical business rule:** Only company admins can assign roles (spec line 186)

- Write test expecting 403 when non-admin tries to assign role
- Write test expecting success when admin assigns role
- Implement ValidateUserCompanyAccessTask checking if auth user has 'admin' role for target company
- Implement AssignUserRoleAction with authorization check before calling AssignUserRoleTask
- Verify tests pass, commit

### Task 2.8: Create API Controllers

**Files:**
- Create: `app/Containers/Finance/Auth/UI/API/Controllers/CreateCompanyController.php`
- Create: `app/Containers/Finance/Auth/UI/API/Controllers/ListCompaniesController.php`
- Create: `app/Containers/Finance/Auth/UI/API/Controllers/AssignRoleController.php`

Each controller follows Porto pattern: inject Action, call Action->run(), return Response with Transformer

### Task 2.9: Create Request Validators

**Files:**
- Create: `app/Containers/Finance/Auth/UI/API/Requests/CreateCompanyRequest.php`
- Create: `app/Containers/Finance/Auth/UI/API/Requests/ListCompaniesRequest.php`
- Create: `app/Containers/Finance/Auth/UI/API/Requests/AssignRoleRequest.php`

**CreateCompanyRequest validation rules:**
```php
public function rules(): array
{
    return [
        'code' => 'required|string|max:20|unique:companies,code|alpha_num',
        'name' => 'required|string|max:100',
        'fiscal_year_start' => 'required|integer|min:1|max:12',
    ];
}
```

**AssignRoleRequest validation rules:**
```php
public function rules(): array
{
    return [
        'user_id' => 'required|exists:users,id',
        'company_id' => 'required|exists:companies,id',
        'role' => 'required|in:admin,accountant,auditor,viewer',
    ];
}
```

### Task 2.10: Create Transformers and Routes

**Files:**
- Create: `app/Containers/Finance/Auth/UI/API/Transformers/CompanyTransformer.php`
- Create: `app/Containers/Finance/Auth/UI/API/Transformers/UserCompanyRoleTransformer.php`
- Create: `app/Containers/Finance/Auth/UI/API/Routes/CreateCompany.v1.private.php`
- Create: `app/Containers/Finance/Auth/UI/API/Routes/ListCompanies.v1.private.php`
- Create: `app/Containers/Finance/Auth/UI/API/Routes/AssignRole.v1.private.php`

**CompanyTransformer:**
```php
public function transform(Company $company): array
{
    return [
        'id' => $company->id,
        'code' => $company->code,
        'name' => $company->name,
        'fiscal_year_start' => $company->fiscal_year_start,
        'status' => $company->status,
        'created_at' => $company->created_at?->toIso8601String(),
    ];
}
```

**Routes:** All under `/api/v1/auth/` prefix, NO tenant middleware (these routes don't require X-Company-Id header)

---

## PHASE 3: Finance/Foundation - Accounts

### Task 3.1: Create Foundation Container Structure

- [ ] **Step 1: Create Foundation container directories**

```bash
mkdir -p app/Containers/Finance/Foundation/{Models,Data/{Migrations,Repositories,Factories,Seeders},Actions,Tasks,UI/API/{Controllers,Requests,Transformers,Routes},Tests/Functional}
```

- [ ] **Step 2: Create composer.json**

```json
{
    "name": "finance/foundation",
    "description": "Finance Foundation Container",
    "type": "container",
    "require": {}
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Containers/Finance/Foundation/
git commit -m "feat(foundation): create Foundation container structure"
```

---

### Task 3.2: Create Account Model and Migration

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/Account.php`
- Create: `app/Containers/Finance/Foundation/Data/Migrations/*_create_accounts_table.php`

- [ ] **Step 1: Create accounts migration**

```bash
php artisan make:migration create_accounts_table --path=app/Containers/Finance/Foundation/Data/Migrations
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->tinyInteger('level');
            $table->enum('element_type', ['asset', 'liability', 'equity', 'income', 'expense', 'cost']);
            $table->enum('balance_direction', ['debit', 'credit']);
            $table->boolean('is_detail')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_aux')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'parent_id']);
            $table->index(['company_id', 'is_active', 'is_detail']);

            $table->foreign('parent_id')->references('id')->on('accounts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 3: Create Account model with BelongsToCompany trait**

```php
<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'level',
        'element_type',
        'balance_direction',
        'is_detail',
        'is_active',
        'has_aux',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_detail' => 'boolean',
        'is_active' => 'boolean',
        'has_aux' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    protected static function booted(): void
    {
        // Auto-update parent's is_detail when child is created
        static::created(function (Account $account) {
            if ($account->parent_id) {
                Account::withoutGlobalScopes()
                    ->where('id', $account->parent_id)
                    ->update(['is_detail' => false]);
            }
        });
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Containers/Finance/Foundation/Models/Account.php app/Containers/Finance/Foundation/Data/Migrations/
git commit -m "feat(foundation): add Account model with hierarchy support"
```

---

### Task 3.3: Create CreateAccountAction with Hierarchy Logic

**Files:**
- Create: `app/Containers/Finance/Foundation/Actions/CreateAccountAction.php`
- Create: `app/Containers/Finance/Foundation/Tasks/ValidateAccountCodeTask.php`
- Create: `app/Containers/Finance/Foundation/Tasks/CalculateAccountLevelTask.php`
- Create: `app/Containers/Finance/Foundation/Tasks/CreateAccountTask.php`

**Critical business rules (spec lines 232-246):**
- Code format: 4-digit segments (1001, 100101, 10010101, 1001010101)
- Level auto-calculated from parent (root=1, child=parent.level+1)
- element_type/balance_direction required for level 1, inherited from parent for children
- Maximum 4 levels deep

- [ ] **Step 1: Write failing test**

```php
public function testCreateAccountCalculatesLevelFromParent(): void
{
    $parent = Account::factory()->create(['code' => '1001', 'level' => 1]);

    $data = [
        'code' => '100101',
        'name' => 'Child Account',
        'parent_id' => $parent->id,
    ];

    $action = app(CreateAccountAction::class);
    $account = $action->run($data);

    $this->assertEquals(2, $account->level);
    $this->assertEquals($parent->element_type, $account->element_type);
    $this->assertEquals($parent->balance_direction, $account->balance_direction);
}

public function testCreateAccountValidatesCodeFormat(): void
{
    $this->expectException(ValidationException::class);

    $action = app(CreateAccountAction::class);
    $action->run(['code' => '123', 'name' => 'Invalid']); // Not 4-digit segments
}
```

- [ ] **Step 2: Implement ValidateAccountCodeTask**

```php
public function run(string $code): void
{
    // Validate 4-digit segments
    if (!preg_match('/^(\d{4})+$/', $code)) {
        throw ValidationException::withMessages([
            'code' => 'Account code must be 4-digit segments (e.g., 1001, 100101)'
        ]);
    }

    // Validate max 4 levels (16 digits)
    if (strlen($code) > 16) {
        throw ValidationException::withMessages([
            'code' => 'Account code cannot exceed 4 levels (16 digits)'
        ]);
    }
}
```

- [ ] **Step 3: Implement CalculateAccountLevelTask**

```php
public function run(?int $parentId, string $code): array
{
    $level = strlen($code) / 4;

    if ($parentId) {
        $parent = Account::findOrFail($parentId);
        return [
            'level' => $level,
            'element_type' => $parent->element_type,
            'balance_direction' => $parent->balance_direction,
        ];
    }

    return ['level' => $level];
}
```

- [ ] **Step 4: Implement CreateAccountAction**

```php
public function run(array $data): Account
{
    $this->validateAccountCodeTask->run($data['code']);

    $calculated = $this->calculateAccountLevelTask->run(
        $data['parent_id'] ?? null,
        $data['code']
    );

    return $this->createAccountTask->run(array_merge($data, $calculated));
}
```

- [ ] **Step 5: Run tests, commit**

### Task 3.4: Create UpdateAccountAction with Immutability Rules

**Critical business rule (spec line 366):** Only name and is_active can be updated - code/parent/is_detail are immutable

- [ ] **Step 1: Write test enforcing immutability**

```php
public function testUpdateAccountOnlyAllowsNameAndIsActive(): void
{
    $account = Account::factory()->create(['code' => '1001', 'name' => 'Original']);

    $action = app(UpdateAccountAction::class);
    $updated = $action->run($account->id, [
        'name' => 'Updated Name',
        'code' => '9999', // Should be ignored
        'is_active' => false,
    ]);

    $this->assertEquals('Updated Name', $updated->name);
    $this->assertEquals('1001', $updated->code); // Unchanged
    $this->assertFalse($updated->is_active);
}
```

- [ ] **Step 2: Implement UpdateAccountTask with field whitelist**

```php
public function run(int $id, array $data): Account
{
    $account = Account::findOrFail($id);

    // Only allow updating name and is_active
    $account->update(Arr::only($data, ['name', 'is_active']));

    return $account->fresh();
}
```

- [ ] **Step 3: Run test, commit**

### Task 3.5: Create DeactivateAccountAction with Children Validation

**Critical business rule (spec line 239-240):** Cannot delete if has children

- [ ] **Step 1: Write test preventing deactivation with children**

```php
public function testDeactivateAccountFailsIfHasChildren(): void
{
    $parent = Account::factory()->create();
    $child = Account::factory()->create(['parent_id' => $parent->id]);

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Cannot deactivate account with children');

    $action = app(DeactivateAccountAction::class);
    $action->run($parent->id);
}
```

- [ ] **Step 2: Implement ValidateAccountHasNoChildrenTask**

```php
public function run(int $accountId): void
{
    $hasChildren = Account::where('parent_id', $accountId)->exists();

    if ($hasChildren) {
        throw ValidationException::withMessages([
            'account' => 'Cannot deactivate account with children'
        ]);
    }
}
```

- [ ] **Step 3: Implement DeactivateAccountAction**

```php
public function run(int $id): Account
{
    $this->validateAccountHasNoChildrenTask->run($id);

    $account = Account::findOrFail($id);
    $account->update(['is_active' => false]);

    return $account;
}
```

- [ ] **Step 4: Run test, commit**

### Task 3.6-3.7: ListAccountsAction and FindAccountByIdAction

Following established TDD pattern:
- ListAccountsAction: filters by parent_id, is_active, is_detail with pagination
- FindAccountByIdAction: retrieves single account with parent/children eager-loaded

### Task 3.8-3.10: Account API Layer

- AccountRepository with searchable fields
- Controllers calling Actions
- Requests with validation (code format, max levels)
- AccountTransformer
- Routes under `/api/v1/accounts` with tenant middleware

---

## PHASE 4: Finance/Foundation - Auxiliary Accounting

### Task 4.1: Create AuxCategory Model

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/AuxCategory.php`
- Create: `app/Containers/Finance/Foundation/Data/Migrations/*_create_aux_categories_table.php`

- [ ] **Step 1: Create migration**

```php
Schema::create('aux_categories', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->index();
    $table->string('code', 20);
    $table->string('name', 50);
    $table->boolean('is_system')->default(false);
    $table->timestamps();

    $table->unique(['company_id', 'code']);
});
```

- [ ] **Step 2: Create model with BelongsToCompany**

```php
<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

class AuxCategory extends Model
{
    use BelongsToCompany;

    protected $fillable = ['code', 'name', 'is_system'];
    protected $casts = ['is_system' => 'boolean'];
}
```

- [ ] **Step 3: Run migration and commit**

---

### Task 4.2-4.5: AuxCategory CRUD

Following established TDD pattern:
- **Task 4.2**: CreateAuxCategoryAction
- **Task 4.3**: UpdateAuxCategoryAction - validates is_system=false before allowing updates
- **Task 4.4**: ListAuxCategoriesAction
- **Task 4.5**: API layer (Repository, Controller, Request, Transformer, Routes)

**Critical validation for Task 4.3:**
```php
public function run(int $id, array $data): AuxCategory
{
    $category = AuxCategory::findOrFail($id);

    if ($category->is_system) {
        throw ValidationException::withMessages([
            'category' => 'System categories cannot be modified'
        ]);
    }

    $category->update($data);
    return $category;
}
```

---

### Task 4.6: Create AuxItem Model

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/AuxItem.php`
- Create migration with company_id, aux_category_id, code, name, parent_id, is_active, extra (jsonb)

Migration includes:
- Unique constraint: (company_id, aux_category_id, code)
- Index: (company_id, aux_category_id, is_active)
- Index: (company_id, aux_category_id, parent_id)
- Foreign key: aux_category_id references aux_categories

Model uses BelongsToCompany trait, supports hierarchy via parent_id

---

### Task 4.7-4.10: AuxItem CRUD

Following established TDD pattern with hierarchy support similar to Account model

---

### Task 4.11: Create AccountAuxCategory Pivot

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/AccountAuxCategory.php`
- Create migration

Migration:
```php
Schema::create('account_aux_categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('account_id')->constrained()->onDelete('restrict');
    $table->foreignId('aux_category_id')->constrained()->onDelete('restrict');
    $table->boolean('is_required')->default(true);
    $table->tinyInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['account_id', 'aux_category_id']);
});
```

---

### Task 4.12-4.13: AccountAuxCategory Actions

- **Task 4.12**: AttachAuxCategoryToAccountAction - validates account.has_aux=true
- **Task 4.13**: DetachAuxCategoryFromAccountAction + API layer

---

## PHASE 5: Finance/Foundation - Periods

### Task 5.1: Create Period Model

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/Period.php`
- Create migration

Migration:
```php
Schema::create('periods', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->index();
    $table->smallInteger('fiscal_year');
    $table->tinyInteger('period_number');
    $table->date('start_date');
    $table->date('end_date');
    $table->enum('status', ['open', 'closed', 'locked'])->default('open');
    $table->timestamp('closed_at')->nullable();
    $table->foreignId('closed_by')->nullable()->constrained('users');
    $table->timestamps();

    $table->unique(['company_id', 'fiscal_year', 'period_number']);
    $table->index(['company_id', 'status']);
});
```

Model with BelongsToCompany trait

---

### Task 5.2: Create CreatePeriodAction with Overlap Validation

**Critical business rules (spec lines 347-355):**
- Periods cannot overlap for same company
- Unique (company_id, fiscal_year, period_number)
- Only one period can be 'open' at a time

- [ ] **Step 1: Write test preventing overlapping periods**

```php
public function testCreatePeriodFailsIfOverlaps(): void
{
    Period::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ]);

    $this->expectException(ValidationException::class);

    $action = app(CreatePeriodAction::class);
    $action->run([
        'fiscal_year' => 2026,
        'period_number' => 2,
        'start_date' => '2026-01-15', // Overlaps with period 1
        'end_date' => '2026-02-15',
    ]);
}
```

- [ ] **Step 2: Implement ValidatePeriodNoOverlapTask**

```php
public function run(string $startDate, string $endDate, int $companyId): void
{
    $overlaps = Period::where('company_id', $companyId)
        ->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate);
                });
        })
        ->exists();

    if ($overlaps) {
        throw ValidationException::withMessages([
            'period' => 'Period dates overlap with existing period'
        ]);
    }
}
```

- [ ] **Step 3: Implement CreatePeriodAction, run test, commit**

---

### Task 5.3: Create InitializeFiscalYearAction

**Critical business rule (spec lines 562-572):** Respect company's fiscal_year_start setting

- [ ] **Step 1: Write test for fiscal year initialization**

```php
public function testInitializeFiscalYearRespectsCompanyFiscalYearStart(): void
{
    $company = Company::factory()->create(['fiscal_year_start' => 4]); // April
    app()->instance('current.company_id', $company->id);

    $action = app(InitializeFiscalYearAction::class);
    $periods = $action->run(2026);

    $this->assertCount(12, $periods);
    $this->assertEquals('2026-04-01', $periods[0]->start_date->format('Y-m-d'));
    $this->assertEquals('2027-03-31', $periods[11]->end_date->format('Y-m-d'));
    $this->assertEquals('open', $periods[0]->status);
    $this->assertEquals('closed', $periods[1]->status);
}
```

- [ ] **Step 2: Implement InitializeFiscalYearAction**

```php
public function run(int $fiscalYear): Collection
{
    $companyId = app('current.company_id');
    $company = Company::findOrFail($companyId);
    $startMonth = $company->fiscal_year_start;

    $periods = collect();

    for ($i = 0; $i < 12; $i++) {
        $periodNumber = $i + 1;
        $monthOffset = $startMonth - 1 + $i;
        $year = $fiscalYear + floor($monthOffset / 12);
        $month = ($monthOffset % 12) + 1;

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $period = Period::create([
            'company_id' => $companyId,
            'fiscal_year' => $fiscalYear,
            'period_number' => $periodNumber,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $periodNumber === 1 ? 'open' : 'closed',
        ]);

        $periods->push($period);
    }

    return $periods;
}
```

- [ ] **Step 3: Run test, commit**

---

### Task 5.4: Create ClosePeriodAction with Status Transition Validation

**Critical business rules (spec lines 352, 355, 358):**
- Status transitions: open → closed → locked (one-way only)
- Only one period can be 'open' at a time
- P0: closing just transitions status (no voucher validation)

- [ ] **Step 1: Write test for period closing**

```php
public function testClosePeriodTransitionsOpenToClosed(): void
{
    $period = Period::factory()->create(['status' => 'open']);

    $action = app(ClosePeriodAction::class);
    $closed = $action->run($period->id);

    $this->assertEquals('closed', $closed->status);
    $this->assertNotNull($closed->closed_at);
    $this->assertEquals(auth()->id(), $closed->closed_by);
}

public function testClosePeriodFailsIfNotOpen(): void
{
    $period = Period::factory()->create(['status' => 'closed']);

    $this->expectException(ValidationException::class);

    $action = app(ClosePeriodAction::class);
    $action->run($period->id);
}
```

- [ ] **Step 2: Implement ClosePeriodAction**

```php
public function run(int $id): Period
{
    $period = Period::findOrFail($id);

    if ($period->status !== 'open') {
        throw ValidationException::withMessages([
            'period' => 'Only open periods can be closed'
        ]);
    }

    $period->update([
        'status' => 'closed',
        'closed_at' => now(),
        'closed_by' => auth()->id(),
    ]);

    return $period;
}
```

- [ ] **Step 3: Run test, commit**

---

### Task 5.5-5.6: Period API Layer

Following established pattern:
- ListPeriodsAction with filters (fiscal_year, status)
- FindPeriodByIdAction
- API layer (Repository, Controller, Request, Transformer, Routes)

---

## PHASE 6: Seeders & Integration

### Task 6.1: Create CompanySeeder

```php
<?php

namespace App\Containers\Finance\Auth\Data\Seeders;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Seeders\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => '默认公司',
                'fiscal_year_start' => 1,
                'status' => 'active',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        UserCompanyRole::firstOrCreate(
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
            ],
            [
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
```

---

### Task 6.2: Create AuxCategorySeeder

```php
<?php

namespace App\Containers\Finance\Foundation\Data\Seeders;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Seeders\Seeder;

class AuxCategorySeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
```

---

### Task 6.3: Create AccountSeeder

```php
<?php

namespace App\Containers\Finance\Foundation\Data\Seeders;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Seeders\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('code', 'DEFAULT')->first();

        if (!$company) {
            return;
        }

        $accounts = [
            // Assets (1xxx)
            ['code' => '1001', 'name' => '库存现金', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1002', 'name' => '银行存款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1012', 'name' => '其他货币资金', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1101', 'name' => '交易性金融资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1121', 'name' => '应收票据', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1122', 'name' => '应收账款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1123', 'name' => '预付账款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1221', 'name' => '其他应收款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1231', 'name' => '坏账准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1401', 'name' => '材料采购', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1402', 'name' => '在途物资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1403', 'name' => '原材料', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1404', 'name' => '材料成本差异', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1405', 'name' => '库存商品', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1406', 'name' => '发出商品', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1407', 'name' => '商品进销差价', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1408', 'name' => '委托加工物资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1411', 'name' => '周转材料', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1471', 'name' => '存货跌价准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1501', 'name' => '持有至到期投资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1502', 'name' => '持有至到期投资减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1503', 'name' => '可供出售金融资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1511', 'name' => '长期股权投资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1512', 'name' => '长期股权投资减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1521', 'name' => '投资性房地产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1531', 'name' => '长期应收款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1601', 'name' => '固定资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1602', 'name' => '累计折旧', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1603', 'name' => '固定资产减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1604', 'name' => '在建工程', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1605', 'name' => '工程物资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1606', 'name' => '固定资产清理', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1701', 'name' => '无形资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1702', 'name' => '累计摊销', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1703', 'name' => '无形资产减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
            ['code' => '1711', 'name' => '商誉', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1801', 'name' => '长期待摊费用', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1811', 'name' => '递延所得税资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
            ['code' => '1901', 'name' => '待处理财产损溢', 'element_type' => 'asset', 'balance_direction' => 'debit'],

            // Liabilities (2xxx)
            ['code' => '2001', 'name' => '短期借款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2201', 'name' => '应付票据', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2202', 'name' => '应付账款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2203', 'name' => '预收账款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2211', 'name' => '应付职工薪酬', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2221', 'name' => '应交税费', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2231', 'name' => '应付利息', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2232', 'name' => '应付股利', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2241', 'name' => '其他应付款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2501', 'name' => '长期借款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2502', 'name' => '应付债券', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2701', 'name' => '长期应付款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2702', 'name' => '未确认融资费用', 'element_type' => 'liability', 'balance_direction' => 'debit'],
            ['code' => '2711', 'name' => '专项应付款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2801', 'name' => '预计负债', 'element_type' => 'liability', 'balance_direction' => 'credit'],
            ['code' => '2901', 'name' => '递延所得税负债', 'element_type' => 'liability', 'balance_direction' => 'credit'],

            // Equity (4xxx)
            ['code' => '4001', 'name' => '实收资本', 'element_type' => 'equity', 'balance_direction' => 'credit'],
            ['code' => '4002', 'name' => '资本公积', 'element_type' => 'equity', 'balance_direction' => 'credit'],
            ['code' => '4101', 'name' => '盈余公积', 'element_type' => 'equity', 'balance_direction' => 'credit'],
            ['code' => '4103', 'name' => '本年利润', 'element_type' => 'equity', 'balance_direction' => 'credit'],
            ['code' => '4104', 'name' => '利润分配', 'element_type' => 'equity', 'balance_direction' => 'credit'],

            // Cost (5xxx)
            ['code' => '5001', 'name' => '生产成本', 'element_type' => 'cost', 'balance_direction' => 'debit'],
            ['code' => '5101', 'name' => '制造费用', 'element_type' => 'cost', 'balance_direction' => 'debit'],
            ['code' => '5201', 'name' => '劳务成本', 'element_type' => 'cost', 'balance_direction' => 'debit'],
            ['code' => '5301', 'name' => '研发支出', 'element_type' => 'cost', 'balance_direction' => 'debit'],

            // Income (6xxx - revenue)
            ['code' => '6001', 'name' => '主营业务收入', 'element_type' => 'income', 'balance_direction' => 'credit'],
            ['code' => '6051', 'name' => '其他业务收入', 'element_type' => 'income', 'balance_direction' => 'credit'],
            ['code' => '6101', 'name' => '公允价值变动损益', 'element_type' => 'income', 'balance_direction' => 'credit'],
            ['code' => '6111', 'name' => '投资收益', 'element_type' => 'income', 'balance_direction' => 'credit'],
            ['code' => '6301', 'name' => '营业外收入', 'element_type' => 'income', 'balance_direction' => 'credit'],

            // Expense (6xxx - expenses)
            ['code' => '6401', 'name' => '主营业务成本', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6402', 'name' => '其他业务成本', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6403', 'name' => '税金及附加', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6601', 'name' => '销售费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6602', 'name' => '管理费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6603', 'name' => '财务费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6701', 'name' => '资产减值损失', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6711', 'name' => '营业外支出', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6801', 'name' => '所得税费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ['code' => '6901', 'name' => '以前年度损益调整', 'element_type' => 'expense', 'balance_direction' => 'debit'],
        ];

        foreach ($accounts as $accountData) {
            Account::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => $accountData['code'],
                ],
                [
                    'name' => $accountData['name'],
                    'level' => 1,
                    'element_type' => $accountData['element_type'],
                    'balance_direction' => $accountData['balance_direction'],
                    'is_detail' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
```

---

### Task 6.4: Create PeriodSeeder

```php
<?php

namespace App\Containers\Finance\Foundation\Data\Seeders;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Seeders\Seeder;
use Carbon\Carbon;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('code', 'DEFAULT')->first();

        if (!$company) {
            return;
        }

        $fiscalYear = 2026;
        $startMonth = $company->fiscal_year_start;

        for ($i = 0; $i < 12; $i++) {
            $periodNumber = $i + 1;
            $monthOffset = $startMonth - 1 + $i;
            $year = $fiscalYear + floor($monthOffset / 12);
            $month = ($monthOffset % 12) + 1;

            $startDate = Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();

            Period::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'fiscal_year' => $fiscalYear,
                    'period_number' => $periodNumber,
                ],
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $periodNumber === 1 ? 'open' : 'closed',
                ]
            );
        }
    }
}
```

---

### Task 6.5: Integration Tests

Create: `tests/Integration/Containers/Finance/MultiTenantIsolationTest.php`

```php
<?php

namespace Tests\Integration\Containers\Finance;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tests\PhpUnit\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    public function testUserCannotSeeOtherCompanyData(): void
    {
        // Arrange
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->create();

        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company1->id,
            'role' => 'admin',
        ]);

        app()->instance('current.company_id', $company1->id);
        $account1 = Account::factory()->create(['company_id' => $company1->id]);

        app()->instance('current.company_id', $company2->id);
        $account2 = Account::factory()->create(['company_id' => $company2->id]);

        // Act
        app()->instance('current.company_id', $company1->id);
        $accounts = Account::all();

        // Assert
        $this->assertCount(1, $accounts);
        $this->assertEquals($account1->id, $accounts->first()->id);
    }

    public function testAccountHierarchyAutoUpdatesParentIsDetail(): void
    {
        $company = Company::factory()->create();
        app()->instance('current.company_id', $company->id);

        $parent = Account::factory()->create(['is_detail' => true]);
        $this->assertTrue($parent->is_detail);

        $child = Account::factory()->create(['parent_id' => $parent->id]);

        $parent->refresh();
        $this->assertFalse($parent->is_detail);
    }

    public function testPeriodStatusTransitionsAreOneWay(): void
    {
        $company = Company::factory()->create();
        app()->instance('current.company_id', $company->id);

        $period = Period::factory()->create(['status' => 'open']);

        $period->update(['status' => 'closed']);
        $this->assertEquals('closed', $period->status);

        $period->update(['status' => 'locked']);
        $this->assertEquals('locked', $period->status);

        // Cannot reopen locked period
        $this->expectException(\Exception::class);
        $period->update(['status' => 'open']);
    }
}
```

---

## Execution Strategy

Given the large scope, I recommend:

1. **Use subagent-driven development** - dispatch fresh subagent per task for parallel execution
2. **Review after each phase** - validate Phase 1 complete before starting Phase 2
3. **Run tests frequently** - after every 3-5 tasks
4. **Commit often** - after every task completion

---

## Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test tests/Unit/Ship/
php artisan test tests/Unit/Containers/Finance/Auth/
php artisan test tests/Functional/Containers/Finance/Auth/

# Run with coverage
php artisan test --coverage
```

---

## Success Criteria

P0 complete when:
- ✅ All migrations run successfully
- ✅ All unit tests pass
- ✅ All functional tests pass
- ✅ Seeders create default company with Chart of Accounts
- ✅ API endpoints respond correctly with proper tenant isolation
- ✅ Multi-tenant middleware validates company access
