# Codebase Structure

This document provides a high-level map of the directory structure and the purpose of key components.

## Project Root

| Path | Purpose |
| :--- | :--- |
| `app/` | Backend application logic (Laravel/Apiato). |
| `frontend/` | React SPA source code. |
| `config/` | Laravel configuration files. |
| `database/` | Global migrations (OAuth/Passport) and seeders. |
| `docs/` | Technical documentation and progress reports. |
| `Modules/` | Architectural specifications (CLAUDE.md) for domain modules. |
| `public/` | Web server entry point (`index.php`) and static assets. |
| `routes/` | Global Laravel routes (rarely used in Apiato). |
| `tests/` | Global test suite (Functional, Integration, Unit). |

## Backend: `app/Containers/Finance`
The core business logic resides here, organized by accounting sub-modules.

| Sub-module | Description |
| :--- | :--- |
| `GeneralLedger` | Account management, period closing, and financial reporting. |
| `Voucher` | Entry and management of accounting vouchers (Journal, Receipt, Payment). |
| `AccountsPayable` | Vendor invoices and payments. |
| `AccountsReceivable` | Customer invoices and receipts. |
| `FixedAsset` | Asset depreciation and tracking. |

## Backend: `app/Ship`
Shared application code.

| Directory | Purpose |
| :--- | :--- |
| `Parents/` | Base classes for all Porto components (Actions, Tasks, Models, etc.). |
| `Middleware/` | Global middleware (e.g., `SwitchTenantMiddleware.php`). |
| `Exceptions/` | Shared error handling logic. |

## Frontend: `frontend/src`
Organized by standard React patterns.

| Directory | Purpose |
| :--- | :--- |
| `api/` | API client configurations and Axios interceptors. |
| `components/` | Reusable UI components. |
| `context/` | React Context providers (Auth, Theme). |
| `pages/` | Top-level view components (BalanceSheet, Ledger, etc.). |
| `types/` | TypeScript interface and type definitions. |
