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

### Task 2.6-2.10: Complete Finance/Auth API Layer

**Note:** Following the same TDD pattern as Task 2.5, implement:

- **Task 2.6**: ListUserCompaniesAction + FindUserCompaniesTask
- **Task 2.7**: AssignUserRoleAction + ValidateUserCompanyAccessTask
- **Task 2.8**: Controllers (CreateCompanyController, ListCompaniesController, AssignRoleController)
- **Task 2.9**: Requests (CreateCompanyRequest, ListCompaniesRequest, AssignRoleRequest) with validation rules
- **Task 2.10**: Transformers (CompanyTransformer, UserCompanyRoleTransformer) + Routes

Each task follows 5-7 steps: Write test → Run (fail) → Implement → Run (pass) → Commit

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

### Task 3.3-3.7: Account CRUD Actions

**Note:** Following TDD pattern, implement:

- **Task 3.3**: CreateAccountAction - validates code format, calculates level from parent, inherits element_type/balance_direction
- **Task 3.4**: ListAccountsAction - filters by parent_id, is_active, is_detail with pagination
- **Task 3.5**: FindAccountByIdAction - retrieves single account with parent/children relationships
- **Task 3.6**: UpdateAccountAction - allows updating name and is_active only (code/parent immutable)
- **Task 3.7**: DeactivateAccountAction - validates no children exist before deactivation

Each task: Write test → Run (fail) → Implement Task → Implement Action → Run (pass) → Commit

---

### Task 3.8-3.10: Account API Layer

- **Task 3.8**: AccountRepository + AccountFactory
- **Task 3.9**: Controllers + Requests with validation (code format: 4-digit segments, max 4 levels)
- **Task 3.10**: AccountTransformer + Routes with tenant middleware

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

- **Task 4.2**: CreateAuxCategoryAction
- **Task 4.3**: UpdateAuxCategoryAction - prevents editing system categories
- **Task 4.4**: ListAuxCategoriesAction
- **Task 4.5**: API layer (Repository, Controller, Request, Transformer, Routes)

---

### Task 4.6: Create AuxItem Model

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/AuxItem.php`
- Create migration with company_id, aux_category_id, code, name, parent_id, is_active, extra (jsonb)

---

### Task 4.7-4.10: AuxItem CRUD

- **Task 4.7**: CreateAuxItemAction - validates category exists, supports hierarchy
- **Task 4.8**: UpdateAuxItemAction
- **Task 4.9**: DeactivateAuxItemAction
- **Task 4.10**: API layer with nested routes (/aux-categories/{id}/items)

---

### Task 4.11: Create AccountAuxCategory Pivot

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/AccountAuxCategory.php`
- Create migration with account_id, aux_category_id, is_required, sort_order

---

### Task 4.12-4.13: AccountAuxCategory Actions

- **Task 4.12**: AttachAuxCategoryToAccountAction - validates account has_aux=true
- **Task 4.13**: DetachAuxCategoryFromAccountAction + API layer

---

## PHASE 5: Finance/Foundation - Periods

### Task 5.1: Create Period Model

**Files:**
- Create: `app/Containers/Finance/Foundation/Models/Period.php`
- Create migration with company_id, fiscal_year, period_number, start_date, end_date, status, closed_at, closed_by

---

### Task 5.2-5.6: Period CRUD

- **Task 5.2**: CreatePeriodAction - validates no overlap, unique (company_id, fiscal_year, period_number)
- **Task 5.3**: InitializeFiscalYearAction - creates 12 periods respecting fiscal_year_start
- **Task 5.4**: ClosePeriodAction - validates only one open period, transitions open→closed
- **Task 5.5**: ListPeriodsAction - filters by fiscal_year, status
- **Task 5.6**: API layer (Repository, Controller, Request, Transformer, Routes)

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

Seeds 6 system categories (customer, supplier, dept, employee, inventory, project) for default company.

---

### Task 6.3: Create AccountSeeder

Seeds 60+ level-1 accounts from 新会计准则 for default company with proper element_type and balance_direction.

---

### Task 6.4: Create PeriodSeeder

Creates 12 periods for 2026 respecting company's fiscal_year_start, only period 1 is 'open'.

---

### Task 6.5: Integration Tests

- Test multi-tenant isolation (user A cannot see user B's data)
- Test account hierarchy (parent-child, level calculation, is_detail auto-update)
- Test period status transitions
- Test middleware with valid/invalid company_id
- Test end-to-end: create company → create accounts → attach aux → create periods

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
