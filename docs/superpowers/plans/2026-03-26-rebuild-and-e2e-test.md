# Rebuild and E2E Test Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the database, seed specific credentials, and run all Playwright E2E tests to verify system functionality and multi-tenant isolation.

**Architecture:**
- Reset the database state using `migrate:fresh`.
- Initialize OAuth2 clients using `passport:install`.
- Apply custom seeding using `seed_final_v2.php` via `artisan tinker` to set up specific users and companies.
- Run the frontend in the background and execute E2E tests against it.

**Tech Stack:**
- PHP/Laravel/Apiato
- Passport (OAuth2)
- Node.js/Vite (Frontend)
- Playwright (E2E Testing)

---

### Task 1: Database Reset and Initialization

**Files:**
- Modify: `.env` (verify connection, though not directly editing)

- [ ] **Step 1: Refresh migrations**

Run: `php artisan migrate:fresh`
Expected: Database tables dropped and recreated.

- [ ] **Step 2: Install Passport clients**

Run: `php artisan passport:install`
Expected: Personal access and password grant clients created.

- [ ] **Step 3: Commit**

```bash
# No file changes expected, but good to check status
git status
```

---

### Task 2: Seeding Requested Data

**Files:**
- Use: `seed_final_v2.php`

- [ ] **Step 1: Run custom seeding script**

Run: `php artisan tinker seed_final_v2.php`
Expected: `SEED_SUCCESS` output.

- [ ] **Step 2: Verify users in database (Optional check)**

Run: `php artisan tinker --execute="print_r(App\Containers\AppSection\User\Models\User::pluck('email')->toArray())"`
Expected: Output contains `admin@admin.com`, `admin@acc001.com`, and `admin@acc002.com`.

---

### Task 3: Frontend Server Setup

**Files:**
- Directory: `frontend/`

- [ ] **Step 1: Install frontend dependencies**

Run: `cd frontend && npm install`
Expected: `node_modules` populated.

- [ ] **Step 2: Start frontend server in background**

Run: `cd frontend && npm run dev` (as background process)
Wait for: `localhost:5173` to be ready.

---

### Task 4: Execute E2E Tests

**Files:**
- Directory: `frontend/e2e/`

- [ ] **Step 1: Run all E2E tests**

Run: `cd frontend && npx playwright test`
Expected: All tests pass, especially `comprehensive-v2.spec.ts`.

- [ ] **Step 2: Capture and report results**

Check: `frontend/playwright-report/index.html` (if available) or terminal output summary.
