# Codebase Concerns & Technical Debt

This document identifies cross-cutting concerns, technical debt, and architectural risks in the current codebase.

## High-Risk Architectural Concerns

### 1. Hardcoded Financial Logic
In `app/Containers/Finance/GeneralLedger/UI/API/Controllers/ReportController.php`, account categorization (e.g., Assets vs. Liabilities) is determined by hardcoded string prefixes (e.g., `1xxx` for assets).
*   **Risk**: Fragile and inflexible. Changes to the chart of accounts structure will break reporting logic.
*   **Recommendation**: Use the `element_type` column or a formal account group mapping system.

### 2. Incomplete Core Accounting Processes
Critical actions like `ClosePeriodAction.php` and `YearEndRolloverAction.php` contain `TODO` stubs for essential logic.
*   **Risk**: The system cannot currently perform full-cycle accounting closures (e.g., profit/loss carry-over).
*   **Recommendation**: Implement event-driven checks for sub-module status and complete the rollover logic.

### 3. Tenant Isolation Vulnerability
Tenant isolation relies on manual management via the `X-Company-Id` header and `SwitchTenantMiddleware.php`.
*   **Risk**: Potential data leakage. There are no global Eloquent scopes observed in the models to automatically filter queries by `company_id`.
*   **Recommendation**: Implement a `TenantTrait` with global scopes for all tenant-aware models to ensure data isolation is handled at the framework level.

## Technical Debt

### 1. Frontend Integration Gaps
Several reporting pages (e.g., `BalanceSheetPage.tsx`, `DetailLedgerPage.tsx`) contain `TODO` comments for data loading.
*   **Impact**: Core reporting features are not yet connected to the backend.
*   **Recommendation**: Prioritize the implementation of reporting API endpoints and frontend hooks.

### 2. Inconsistent Testing Patterns
Many tests in `AppSection` contain notes to `move to request test`, indicating a mix of legacy functional tests and newer patterns that hasn't been fully resolved.
*   **Impact**: Confusion for developers and potential gaps in test coverage.
*   **Recommendation**: Standardize on Request tests for API validation and consolidate test suites.

## Architectural Risks

### 1. Sync vs. Async Processing
Heavy financial operations (like closing a period or generating complex reports) appear to be handled synchronously.
*   **Risk**: Performance bottlenecks and request timeouts as data volume grows.
*   **Recommendation**: Offload long-running financial processes to Laravel Queues.
