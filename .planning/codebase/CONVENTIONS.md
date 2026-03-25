# Coding Conventions

**Analysis Date:** 2025-11-20

## Naming Patterns

### Backend (PHP/Laravel/Apiato)

**Files:**
- Actions: `[ActionName]Action.php` (e.g., `CreateAccountAction.php`)
- Tasks: `[TaskName]Task.php` (e.g., `CreateAccountTask.php`)
- Models: `[ModelName].php` (e.g., `Account.php`)
- Controllers: `[ControllerName]Controller.php` (e.g., `AccountController.php`)
- Requests: `[RequestName]Request.php` (e.g., `CreateAccountRequest.php`)
- Migrations: `YYYY_MM_DD_HHMMSS_create_[table_name]_table.php`

**Functions:**
- Action/Task entry point: `run()`
- Controller methods: Standard Laravel (e.g., `index`, `store`, `show`, `update`, `destroy`)
- Helper functions: `camelCase()`

**Variables:**
- Local variables: `$camelCase`
- Class properties: `$camelCase` (private/protected preferred with `readonly` where applicable)

**Types:**
- Use strict typing for parameters and return values in all PHP 8.2+ code.

### Frontend (TypeScript/React)

**Files:**
- Components: `PascalCase.tsx` (e.g., `AuxiliarySelector.tsx`)
- Pages: `PascalCase.tsx` (e.g., `AccountsPayable.tsx`)
- API Clients: `camelCase.ts` (e.g., `accounts.ts`)
- Hooks: `useCamelCase.ts` (e.g., `useAuth.ts`)

**Functions:**
- Component functions: `PascalCase`
- Utility/Helper functions: `camelCase`

**Variables:**
- State variables: `camelCase`
- Constants: `UPPER_SNAKE_CASE`

**Types:**
- Interfaces: `PascalCase` (e.g., `interface AuxiliaryItem`)
- Props: `[ComponentName]Props`

## Code Style

### Backend

**Formatting:**
- Tool: `friendsofphp/php-cs-fixer`
- Configuration: `.php-cs-fixer.dist.php`
- Standard: PSR-12 (implied by PHP-CS-Fixer default)

**Linting:**
- Tool: `phpstan/phpstan` and `vimeo/psalm`
- Rules: `.phpstan.neon.dist`, `psalm.xml`

### Frontend

**Formatting:**
- Tool: `eslint` (with Prettier-like rules often integrated)
- Configuration: `frontend/eslint.config.js`

**Linting:**
- Tool: `eslint` with `@typescript-eslint/parser`

## Import Organization

### Backend
1. PHP standard library
2. Framework (Laravel/Apiato)
3. Internal App namespace (`App\Containers\...`)
4. Internal Ship namespace (`App\Ship\...`)

### Frontend
1. React and React-related libraries
2. UI Libraries (e.g., `antd`)
3. Internal components
4. API clients
5. Styles/Types

## Error Handling

### Backend
- Use custom exceptions where appropriate.
- Repositories wrap database constraint violations in `Apiato\Core\Repositories\Exceptions\ResourceCreationFailed`.
- Actions should handle high-level logic and may throw/rethrow exceptions to be caught by the framework's exception handler.

### Frontend
- Global `ErrorBoundary.tsx` at `frontend/src/components/ErrorBoundary.tsx`.
- API response interceptors handle 401 (unauthorized) globally in `frontend/src/api/client.ts`.

## Logging

**Framework:** `Laravel Logging` (Backend), `console` (Frontend)

**Patterns:**
- Backend: Use `Log` facade for critical errors or audit logs.
- Frontend: Use `console.error` for caught API errors in service/component layer.

## Module Design

### Backend (Porto Architecture)
- **Actions:** Orchestrate business processes, calling multiple Tasks.
- **Tasks:** Single-responsibility logic units.
- **Models:** Eloquent models in `Data/Models`.
- **Repositories:** Data access layer in `Data/Repositories`.

### Frontend
- **Components:** Functional components with Hooks.
- **Pages:** Top-level components in `src/pages`.
- **API Services:** Organized by domain in `src/api/`.

---

*Convention analysis: 2025-11-20*
