# Testing Patterns

**Analysis Date:** 2025-11-20

## Test Framework

### Backend (PHP)

**Runner:**
- PHPUnit 11.0.1
- Config: `phpunit.xml`

**Assertion Library:**
- PHPUnit built-in assertions
- Mockery for object mocking

**Run Commands:**
```bash
./vendor/bin/phpunit         # Run all tests
./vendor/bin/paratest       # Run tests in parallel
```

### Frontend (TypeScript)

**Runner:**
- Custom integration tests (likely using a runner compatible with `describe`/`it`, like Vitest or Jest, though not explicitly in package.json, Vitest is common with Vite).
- Playwright is also present for E2E/Integration.

**Run Commands:**
```bash
# Frontend test script not explicitly defined in package.json but integration tests exist
```

## Test File Organization

### Backend

**Location:**
- `tests/Functional/`: Feature/Integration tests for Action/Controller layers.
- `tests/Integration/`: Integration tests for lower level components.
- `tests/Unit/`: Isolated unit tests.

**Naming:**
- `[Action/TaskName]Test.php` (e.g., `CreateCompanyActionTest.php`)

### Frontend

**Location:**
- `frontend/src/__tests__/`: Integration and unit tests for the frontend.

**Naming:**
- `[filename].test.ts` (e.g., `api.test.ts`)

## Test Structure

### Backend (Functional Test)
```php
public function test_something(): void
{
    // Arrange: Set up initial state, create models, act as user
    $user = User::factory()->create();
    $this->actingAs($user);
    $data = [...];

    // Act: Execute the action or task being tested
    $action = app(CreateCompanyAction::class);
    $company = $action->run($data);

    // Assert: Verify database state or return value
    $this->assertDatabaseHas('companies', ['code' => 'TEST01']);
}
```

### Frontend (Integration Test)
```typescript
describe('API Integration Tests', () => {
  beforeAll(async () => {
    // Setup login/auth
  });

  afterAll(async () => {
    // Cleanup created resources
  });

  it('should create and retrieve account', async () => {
    const response = await accountsApi.create({ ... });
    expect(response.code).toBe(uniqueCode);
  });
});
```

## Mocking

### Backend

**Framework:** Mockery

**Patterns:**
```php
// Mocking a task in an Action test
$mockTask = $this->mock(\App\Containers\Finance\Auth\Tasks\AssignUserRoleTask::class);
$mockTask->shouldReceive('run')
    ->once()
    ->andThrow(new \RuntimeException('Simulated failure'));
```

**What to Mock:**
- Side effects (e.g., external API calls, mailers).
- Tasks within Action tests to isolate failure scenarios (e.g., transaction rollback tests).

### Frontend

**Patterns:**
- Integration tests currently use real API calls with setup/cleanup (based on `api.test.ts`).

## Fixtures and Factories

### Backend

**Test Data:**
- Use Laravel/Apiato Factories in `Data/Factories` of each container.
- `User::factory()->create()` is common.

**Location:**
- Container Factories: `app/Containers/{Section}/{Container}/Data/Factories/`
- Seeders: `app/Containers/{Section}/{Container}/Data/Seeders/`

## Coverage

**Requirements:**
- No strict coverage threshold found in configurations.

## Test Types

**Unit Tests:**
- Focus on isolated Tasks or utility classes.

**Functional/Integration Tests:**
- Backend: Tests Actions with database interaction (refreshing database).
- Frontend: `api.test.ts` tests interaction with the backend API.

---

*Testing analysis: 2025-11-20*
