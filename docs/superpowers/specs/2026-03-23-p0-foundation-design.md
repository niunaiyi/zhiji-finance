# P0 Foundation Design Specification

**Date:** 2026-03-23
**Status:** Approved
**Scope:** Multi-tenant infrastructure, Auth container, Foundation container

---

## Overview

This spec covers the P0 implementation for the zhiji-finance ERP system, establishing the foundational architecture for a multi-tenant financial management system built on Apiato v13 (Porto architecture) with PostgreSQL 15.

**Goals:**
- Multi-tenant isolation via `company_id` with automatic scoping
- Company management and user-company-role assignment
- Complete Chart of Accounts system with auxiliary accounting
- Fiscal period management

**Non-goals (deferred to P1+):**
- Voucher creation/posting
- Financial reports
- Business modules (AR/AP/Inventory/etc)

---

## Architecture

### Technology Stack
- **Framework:** Apiato v13 (Laravel 11 + Porto SAP)
- **Database:** PostgreSQL 15
- **Auth:** Laravel Passport (OAuth2 password grant)
- **Structure:** `app/Containers/Finance/` section with Auth and Foundation containers

### Multi-Tenant Strategy

**Approach:** Header-based tenant selection with middleware validation

- Client sends `X-Company-Id` header on every request to `/api/v1/*` routes
- `SwitchTenantMiddleware` validates user has access to that company
- All business tables include `company_id` column
- `CompanyScope` GlobalScope auto-filters queries by current company
- `BelongsToCompany` trait auto-fills `company_id` on model creation

**Why this approach:**
- Simpler than embedding company_id in JWT claims (Passport v13 doesn't support custom claims easily)
- Stateless - no server-side session storage needed
- Explicit - client controls which company context for each request
- Flexible - easy to switch companies without re-authentication

---

## Ship Layer (Shared Infrastructure)

### 1. BelongsToCompany Trait

**Location:** `app/Ship/Traits/BelongsToCompany.php`

**Responsibilities:**
- Boot `CompanyScope` on model initialization
- Auto-fill `company_id` from `app('current.company_id')` on model creation

**Usage:**
```php
class Account extends Model
{
    use BelongsToCompany;
}
```

### 2. CompanyScope GlobalScope

**Location:** `app/Ship/Scopes/CompanyScope.php`

**Responsibilities:**
- Add `WHERE company_id = ?` to all queries automatically
- Use `app('current.company_id')` as the filter value

**Behavior:**
- Applied to all models using `BelongsToCompany` trait
- Can be bypassed with `withoutGlobalScope(CompanyScope::class)` for admin operations

### 3. SwitchTenantMiddleware

**Location:** `app/Ship/Middleware/SwitchTenantMiddleware.php`

**Responsibilities:**
1. Read `X-Company-Id` header from request (required)
2. Verify authenticated user exists
3. Query `user_company_roles` to verify user has active role for that company
4. Bind `company_id` and `role` to service container:
   - `app()->instance('current.company_id', $companyId)`
   - `app()->instance('current.role', $role)`
5. Return 403 if validation fails

**Applied to:** All `/api/v1/*` routes EXCEPT `/api/v1/auth/*`

**Not applied to:**
- `/api/v1/auth/*` routes (company management - no company context needed yet)
- Apiato's built-in auth routes at `/api/clients/web/login` etc.

---

## Finance/Auth Container

**Location:** `app/Containers/Finance/Auth/`

### Models

#### Company
**Table:** `companies`

**Columns:**
- `id` bigint PK
- `code` varchar(20) unique - company code
- `name` varchar(100) - company name
- `fiscal_year_start` tinyint default 1 - fiscal year start month (1-12)
- `status` enum('active', 'suspended') default 'active'
- `created_at` timestamp
- `updated_at` timestamp

**Validation:**
- `code`: required, unique, alphanumeric, max 20 chars
- `name`: required, max 100 chars
- `fiscal_year_start`: integer 1-12
- `status`: enum values only

**Note:** This table does NOT have `company_id` (it's the tenant root)

#### UserCompanyRole
**Table:** `user_company_roles`

**Columns:**
- `id` bigint PK
- `user_id` bigint FK → users.id
- `company_id` bigint FK → companies.id
- `role` enum('admin', 'accountant', 'auditor', 'viewer')
- `is_active` boolean default true
- `created_at` timestamp
- `updated_at` timestamp

**Indexes:**
- Unique: (user_id, company_id)
- Index: (company_id, is_active)

**Validation:**
- `user_id`: required, exists in users table
- `company_id`: required, exists in companies table
- `role`: required, enum values only
- One user can have only one role per company (enforced by unique constraint)

**Note:** This table does NOT have `company_id` as a tenant field (it's the access control table)

### Actions

#### CreateCompanyAction
**Input:** `CreateCompanyDTO` (code, name, fiscal_year_start)
**Output:** `Company` model

**Steps:**
1. Validate input via `CreateCompanyRequest`
2. Create company record
3. Assign authenticated user as 'admin' role for this company (create `UserCompanyRole`)
4. Return company model

**Transaction:** Yes (company + role assignment)

#### ListUserCompaniesAction
**Input:** Authenticated user
**Output:** Collection of companies

**Steps:**
1. Query `user_company_roles` where `user_id = auth()->id()` and `is_active = true`
2. Eager load related `Company` models
3. Return collection

#### AssignUserRoleAction
**Input:** `AssignRoleDTO` (user_id, company_id, role)
**Output:** `UserCompanyRole` model

**Steps:**
1. Verify authenticated user has 'admin' role for target company
2. Verify target user exists
3. Upsert `user_company_roles` record (update if exists, create if not)
4. Return role assignment

**Authorization:** Only company admins can assign roles

### API Routes

**Base:** `/api/v1/auth/` (no `SwitchTenantMiddleware` - these routes don't require company context)

| Method | Path | Controller | Action | Auth |
|--------|------|------------|--------|------|
| GET | /companies | ListCompaniesController | ListUserCompaniesAction | Required |
| POST | /companies | CreateCompanyController | CreateCompanyAction | Required |
| POST | /companies/{id}/roles | AssignRoleController | AssignUserRoleAction | Required |

**Response format:** Standard Apiato Fractal transformer

---

## Finance/Foundation Container

**Location:** `app/Containers/Finance/Foundation/`

### Models

#### Account (会计科目)
**Table:** `accounts`

**Columns:**
- `id` bigint PK
- `company_id` bigint (tenant field)
- `code` varchar(20) - account code (e.g., "1001", "100101")
- `name` varchar(100) - account name
- `parent_id` bigint nullable FK → accounts.id
- `level` tinyint - hierarchy level (1-4)
- `element_type` enum('asset', 'liability', 'equity', 'income', 'expense', 'cost')
- `balance_direction` enum('debit', 'credit')
- `is_detail` boolean default false - only leaf accounts can be used in vouchers
- `is_active` boolean default true
- `has_aux` boolean default false - whether auxiliary accounting is enabled
- `created_at` timestamp
- `updated_at` timestamp

**Indexes:**
- Unique: (company_id, code)
- Index: (company_id, parent_id)
- Index: (company_id, is_active, is_detail)

**Validation:**
- `code`: required, 4-digit segments (e.g., "1001", "100101", "10010101")
- `name`: required, max 100 chars
- `parent_id`: must exist in same company if provided
- `level`: auto-calculated from parent (root = 1, child = parent.level + 1)
- `element_type`: required for level 1, inherited from parent for children
- `balance_direction`: required for level 1, inherited from parent for children
- `is_detail`: system-managed field, automatically set to true when account has no children, false when children exist. Cannot be manually changed - derived from account hierarchy. When creating a child account, parent's is_detail is automatically set to false.
- Cannot delete account if it has children or has been used in vouchers (soft delete via `is_active`)
  - P0: Only validate "has children" - voucher validation deferred to P1

**Business Rules:**
- Code format: 4 digits per level, no separators (1001, 100101, 10010101, 1001010101)
- Maximum 4 levels deep
- Only `is_detail = true` accounts can be used in voucher lines
- If `has_aux = true`, must have at least one auxiliary category assigned

#### AuxCategory (辅助核算类别)
**Table:** `aux_categories`

**Columns:**
- `id` bigint PK
- `company_id` bigint (tenant field)
- `code` varchar(20) - category code
- `name` varchar(50) - category name
- `is_system` boolean default false - system categories cannot be deleted
- `created_at` timestamp
- `updated_at` timestamp

**Indexes:**
- Unique: (company_id, code)

**System Categories (is_system = true):**
- `customer` - 客户
- `supplier` - 供应商
- `dept` - 部门
- `employee` - 职员
- `inventory` - 存货
- `project` - 项目

**Validation:**
- `code`: required, alphanumeric, max 20 chars
- `name`: required, max 50 chars
- System categories cannot be deleted or have code/name changed

#### AuxItem (辅助核算项目)
**Table:** `aux_items`

**Columns:**
- `id` bigint PK
- `company_id` bigint (tenant field)
- `aux_category_id` bigint FK → aux_categories.id
- `code` varchar(50) - item code
- `name` varchar(100) - item name
- `parent_id` bigint nullable FK → aux_items.id (supports hierarchy)
- `is_active` boolean default true
- `extra` jsonb nullable - extended attributes (e.g., customer contact, employee dept)
- `created_at` timestamp
- `updated_at` timestamp

**Indexes:**
- Unique: (company_id, aux_category_id, code)
- Index: (company_id, aux_category_id, is_active)
- Index: (company_id, aux_category_id, parent_id)

**Validation:**
- `code`: required, max 50 chars
- `name`: required, max 100 chars
- `aux_category_id`: required, must exist in same company
- `parent_id`: must exist in same category if provided
- Cannot delete if used in voucher lines (soft delete via `is_active`)
  - P0: Deletion allowed - voucher validation deferred to P1

**Extra field examples:**
- Customer: `{"contact": "张三", "phone": "13800138000"}`
- Employee: `{"dept_id": 5, "position": "会计"}`

#### AccountAuxCategory (科目辅助核算关联)
**Table:** `account_aux_categories`

**Columns:**
- `id` bigint PK
- `account_id` bigint FK → accounts.id
- `aux_category_id` bigint FK → aux_categories.id
- `is_required` boolean default true - whether this aux is mandatory for voucher lines
- `sort_order` tinyint default 0 - display order

**Indexes:**
- Unique: (account_id, aux_category_id)

**Validation:**
- `account_id`: required, must have `has_aux = true`
- `aux_category_id`: required, must exist in same company
- Cannot delete if account has been used in vouchers with this aux category
  - P0: Deletion allowed - voucher validation deferred to P1

#### Period (会计期间)
**Table:** `periods`

**Columns:**
- `id` bigint PK
- `company_id` bigint (tenant field)
- `fiscal_year` smallint - fiscal year (e.g., 2026)
- `period_number` tinyint - period number (1-12)
- `start_date` date - period start date
- `end_date` date - period end date
- `status` enum('open', 'closed', 'locked') default 'open'
- `closed_at` timestamp nullable
- `closed_by` bigint nullable FK → users.id
- `created_at` timestamp
- `updated_at` timestamp

**Indexes:**
- Unique: (company_id, fiscal_year, period_number)
- Index: (company_id, status)

**Validation:**
- `fiscal_year`: required, 4-digit year
- `period_number`: required, 1-12
- `start_date` / `end_date`: required, end_date > start_date
- Periods cannot overlap for same company
- Status transitions: open → closed → locked (one-way only)

**Business Rules:**
- Only one period can be 'open' at a time per company
- Cannot create vouchers in 'closed' or 'locked' periods
- Cannot reopen a 'locked' period
- Period closing requires all vouchers in that period to be posted (P1+ - in P0, closing just transitions status)

### Actions

Each model has standard CRUD actions following Porto patterns:

**Account Actions:**
- `CreateAccountAction` - create account with auto-calculated level
- `UpdateAccountAction` - update account (name, is_active only - code/parent/is_detail are immutable after creation)
- `ListAccountsAction` - list accounts with optional filters (parent_id, is_active, is_detail)
- `FindAccountByIdAction` - find single account
- `DeactivateAccountAction` - soft delete (set is_active = false)

**AuxCategory Actions:**
- `CreateAuxCategoryAction`
- `UpdateAuxCategoryAction` - prevents editing system categories
- `ListAuxCategoriesAction`
- `FindAuxCategoryByIdAction`

**AuxItem Actions:**
- `CreateAuxItemAction`
- `UpdateAuxItemAction`
- `ListAuxItemsAction` - filter by category_id, parent_id, is_active
- `FindAuxItemByIdAction`
- `DeactivateAuxItemAction`

**AccountAuxCategory Actions:**
- `AttachAuxCategoryToAccountAction` - attach aux category to account
- `DetachAuxCategoryFromAccountAction` - detach aux category
- `ListAccountAuxCategoriesAction` - list aux categories for an account

**Period Actions:**
- `CreatePeriodAction` - create single period
- `InitializeFiscalYearAction` - create 12 periods for a fiscal year
- `ClosePeriodAction` - close current open period (P0: just transitions status; P1+: validates all vouchers posted)
- `ListPeriodsAction` - list periods with filters (fiscal_year, status)
- `FindPeriodByIdAction`

### API Routes

**Base:** `/api/v1/` (all routes require `SwitchTenantMiddleware` + `X-Company-Id` header)

**Accounts:**
| Method | Path | Controller | Action |
|--------|------|------------|--------|
| GET | /accounts | ListAccountsController | ListAccountsAction |
| POST | /accounts | CreateAccountController | CreateAccountAction |
| GET | /accounts/{id} | FindAccountController | FindAccountByIdAction |
| PUT | /accounts/{id} | UpdateAccountController | UpdateAccountAction |
| DELETE | /accounts/{id} | DeactivateAccountController | DeactivateAccountAction |

**Aux Categories:**
| Method | Path | Controller | Action |
|--------|------|------------|--------|
| GET | /aux-categories | ListAuxCategoriesController | ListAuxCategoriesAction |
| POST | /aux-categories | CreateAuxCategoryController | CreateAuxCategoryAction |
| GET | /aux-categories/{id} | FindAuxCategoryController | FindAuxCategoryByIdAction |
| PUT | /aux-categories/{id} | UpdateAuxCategoryController | UpdateAuxCategoryAction |

**Aux Items:**
| Method | Path | Controller | Action |
|--------|------|------------|--------|
| GET | /aux-categories/{categoryId}/items | ListAuxItemsController | ListAuxItemsAction |
| POST | /aux-categories/{categoryId}/items | CreateAuxItemController | CreateAuxItemAction |
| GET | /aux-items/{id} | FindAuxItemController | FindAuxItemByIdAction |
| PUT | /aux-items/{id} | UpdateAuxItemController | UpdateAuxItemAction |
| DELETE | /aux-items/{id} | DeactivateAuxItemController | DeactivateAuxItemAction |

**Account Aux Categories:**
| Method | Path | Controller | Action |
|--------|------|------------|--------|
| GET | /accounts/{id}/aux-categories | ListAccountAuxCategoriesController | ListAccountAuxCategoriesAction |
| POST | /accounts/{id}/aux-categories | AttachAuxCategoryController | AttachAuxCategoryToAccountAction |
| DELETE | /accounts/{id}/aux-categories/{auxCategoryId} | DetachAuxCategoryController | DetachAuxCategoryFromAccountAction |

**Periods:**
| Method | Path | Controller | Action |
|--------|------|------------|--------|
| GET | /periods | ListPeriodsController | ListPeriodsAction |
| POST | /periods | CreatePeriodController | CreatePeriodAction |
| POST | /periods/initialize-year | InitializeFiscalYearController | InitializeFiscalYearAction |
| GET | /periods/{id} | FindPeriodController | FindPeriodByIdAction |
| POST | /periods/{id}/close | ClosePeriodController | ClosePeriodAction |

---

## Database Migrations

### Migration Order
1. `create_companies_table` (no company_id)
2. `create_user_company_roles_table` (no company_id)
3. `create_accounts_table`
4. `create_aux_categories_table`
5. `create_aux_items_table`
6. `create_account_aux_categories_table`
7. `create_periods_table`

### Key Constraints
- All business tables (accounts, aux_categories, aux_items, periods) have `company_id` with index
- Foreign keys use `onDelete('restrict')` to prevent accidental deletion
- Unique constraints include `company_id` to ensure tenant isolation

---

## Seed Data

### CompanySeeder
- Create default company: code="DEFAULT", name="默认公司", fiscal_year_start=1
- Create admin user if not exists: email="admin@example.com", password="password"
- Assign admin user to default company with 'admin' role

### AuxCategorySeeder
- Create 6 system categories for default company:
  - customer (客户)
  - supplier (供应商)
  - dept (部门)
  - employee (职员)
  - inventory (存货)
  - project (项目)

### AccountSeeder
- Create 新会计准则 level 1 accounts for default company (60+ accounts):
  - 1001 库存现金
  - 1002 银行存款
  - 1012 其他货币资金
  - 1101 交易性金融资产
  - 1121 应收票据
  - 1122 应收账款
  - 1123 预付账款
  - 1221 其他应收款
  - 1231 坏账准备
  - 1401 材料采购
  - 1402 在途物资
  - 1403 原材料
  - 1404 材料成本差异
  - 1405 库存商品
  - 1406 发出商品
  - 1407 商品进销差价
  - 1408 委托加工物资
  - 1411 周转材料
  - 1471 存货跌价准备
  - 1501 持有至到期投资
  - 1502 持有至到期投资减值准备
  - 1503 可供出售金融资产
  - 1511 长期股权投资
  - 1512 长期股权投资减值准备
  - 1521 投资性房地产
  - 1531 长期应收款
  - 1601 固定资产
  - 1602 累计折旧
  - 1603 固定资产减值准备
  - 1604 在建工程
  - 1605 工程物资
  - 1606 固定资产清理
  - 1701 无形资产
  - 1702 累计摊销
  - 1703 无形资产减值准备
  - 1711 商誉
  - 1801 长期待摊费用
  - 1811 递延所得税资产
  - 1901 待处理财产损溢
  - 2001 短期借款
  - 2201 应付票据
  - 2202 应付账款
  - 2203 预收账款
  - 2211 应付职工薪酬
  - 2221 应交税费
  - 2231 应付利息
  - 2232 应付股利
  - 2241 其他应付款
  - 2501 长期借款
  - 2502 应付债券
  - 2701 长期应付款
  - 2702 未确认融资费用
  - 2711 专项应付款
  - 2801 预计负债
  - 2901 递延所得税负债
  - 4001 实收资本
  - 4002 资本公积
  - 4101 盈余公积
  - 4103 本年利润
  - 4104 利润分配
  - 5001 生产成本
  - 5101 制造费用
  - 5201 劳务成本
  - 5301 研发支出
  - 6001 主营业务收入
  - 6051 其他业务收入
  - 6101 公允价值变动损益
  - 6111 投资收益
  - 6301 营业外收入
  - 6401 主营业务成本
  - 6402 其他业务成本
  - 6403 税金及附加
  - 6601 销售费用
  - 6602 管理费用
  - 6603 财务费用
  - 6701 资产减值损失
  - 6711 营业外支出
  - 6801 所得税费用
  - 6901 以前年度损益调整

### PeriodSeeder
- Create 12 periods for default company for fiscal year 2026
- Respect the company's `fiscal_year_start` setting (default is 1 = January)
- If fiscal_year_start = 1 (January):
  - Period 1: 2026-01-01 to 2026-01-31, status='open'
  - Period 2: 2026-02-01 to 2026-02-28, status='closed'
  - Period 3-12: subsequent months, status='closed'
- If fiscal_year_start = 4 (April):
  - Period 1: 2026-04-01 to 2026-04-30, status='open'
  - Period 2: 2026-05-01 to 2026-05-31, status='closed'
  - Period 3-12: subsequent months through 2027-03-31, status='closed'
- Only the first period (period_number=1) should have status='open', all others 'closed'
- Periods are opened sequentially as the company progresses through the fiscal year

---

## Testing Strategy

### Unit Tests
- Model validation rules
- Scope application (CompanyScope)
- Trait behavior (BelongsToCompany)

### Feature Tests
- Auth flow: create company, list companies, assign roles
- Account CRUD with hierarchy validation
- Aux category/item CRUD
- Period initialization and closing
- Middleware: SwitchTenantMiddleware with valid/invalid company_id

### Integration Tests
- Multi-tenant isolation: user A cannot see user B's data
- Account hierarchy: parent-child relationships, level calculation
- Period status transitions: open → closed → locked

---

## Security Considerations

1. **Tenant Isolation:** All queries automatically filtered by company_id via GlobalScope
2. **Authorization:** Middleware validates user has active role for requested company
3. **Input Validation:** All requests validated via FormRequest classes
4. **SQL Injection:** Using Eloquent ORM with parameter binding
5. **Mass Assignment:** Models use `$fillable` whitelist
6. **Soft Deletes:** Critical entities use `is_active` flag instead of hard delete

---

## Performance Considerations

1. **Indexes:** All foreign keys and tenant fields (company_id) are indexed
2. **Eager Loading:** List actions should eager load relationships to avoid N+1
3. **Pagination:** All list endpoints return paginated results (default 15 per page)
4. **Query Optimization:** Use `select()` to limit columns when full model not needed

---

## Future Considerations (Out of Scope for P0)

- Account balance caching
- Period locking cascade to related modules
- Audit logging for all changes
- Multi-currency support
- Account import/export
- Bulk operations for aux items

---

## Success Criteria

P0 is complete when:
1. ✅ User can create a company and be assigned as admin
2. ✅ User can list their accessible companies
3. ✅ User can create/update/list accounts with hierarchy
4. ✅ User can create/update/list aux categories and items
5. ✅ User can attach aux categories to accounts
6. ✅ User can initialize fiscal year periods
7. ✅ User can close a period (status transition)
8. ✅ All API requests are properly scoped by company_id
9. ✅ Seed data creates a working default company with standard Chart of Accounts
10. ✅ All feature tests pass

---

## Implementation Notes

- Follow Porto architecture strictly: Actions call Tasks, Controllers call Actions
- All models extend `App\Ship\Parents\Models\Model`
- All controllers extend `App\Ship\Parents\Controllers\ApiController`
- All requests extend `App\Ship\Parents\Requests\Request`
- All transformers extend `App\Ship\Parents\Transformers\Transformer`
- Use Apiato's `apiato:generate:*` commands for scaffolding
- Commit migrations separately from models/logic for easier rollback
