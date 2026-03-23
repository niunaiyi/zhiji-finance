# 企业财务管理系统 · 完整需求文档（PRD）

> 模仿用友 U8 的企业财务管理系统
> 技术栈：Apiato v13 + Porto 架构 + PostgreSQL 15 + React
> 单数据库多账套（company_id 隔离）

---

## 一、技术栈

| 层 | 技术 |
|----|------|
| 基础框架 | Apiato v13（基于 Laravel 11） |
| 架构模式 | Porto SAP（Sections / Containers / Actions / Tasks） |
| 数据库 | PostgreSQL 15 |
| 认证 | Apiato 内置 JWT（Laravel Passport） |
| 前端 | React（已有，在 frontend/ 目录） |

---

## 二、Porto 架构规范

### 2.1 两层结构

```
app/
├── Containers/          # 业务逻辑层（你写的代码都在这里）
│   ├── Finance/         # Section：财务系统
│   │   ├── Foundation/  # Container：基础设置
│   │   ├── Voucher/     # Container：凭证
│   │   ├── ...
│   └── Vendor/          # Section：Apiato 内置容器（不要动）
└── Ship/                # 基础设施层（共享代码，很少改动）
    ├── Parents/         # 所有父类（Action/Task/Model/Controller...）
    ├── Middlewares/
    ├── Events/
    └── ...
```

### 2.2 Container 内部结构

```
app/Containers/Finance/{ContainerName}/
├── Actions/             # 业务操作（每个用例一个 Action）
├── Tasks/               # 可复用的原子操作
├── Models/
├── Data/
│   ├── Migrations/
│   ├── Repositories/
│   ├── Factories/
│   └── Seeders/
├── Events/              # 本 Container 抛出的事件
├── Listeners/           # 监听其他 Container 的事件
├── Notifications/
└── UI/
    └── API/
        ├── Controllers/
        ├── Requests/
        ├── Routes/
        └── Transformers/
```

### 2.3 Porto 组件职责

| 组件 | 职责 | 规则 |
|------|------|------|
| **Action** | 一个完整的业务用例 | 一个 Action 只做一件事，调用多个 Task 组合完成 |
| **Task** | 可复用的原子操作 | 单一职责，可被多个 Action 复用 |
| **Controller** | 接收请求，调用 Action，返回响应 | 禁止写业务逻辑 |
| **Request** | 验证入参 | 含授权检查 |
| **Transformer** | 格式化输出 | 替代 Laravel Resource |
| **Repository** | 数据访问 | 封装所有查询 |
| **Model** | 数据模型 | 必须继承 Ship Parent Model |
| **Event/Listener** | 跨 Container 通信 | Section 间通信的唯一合法方式 |

### 2.4 继承规范（必须遵守）

```php
// Action 必须继承
use App\Ship\Parents\Actions\Action;
class CreateVoucherAction extends Action {}

// Task 必须继承
use App\Ship\Parents\Tasks\Task;
class CreateVoucherTask extends Task {}

// Controller 必须继承
use App\Ship\Parents\Controllers\ApiController;
class VoucherController extends ApiController {}

// Model 必须继承
use App\Ship\Parents\Models\Model;
class Voucher extends Model {}

// Request 必须继承
use App\Ship\Parents\Requests\Request;
class CreateVoucherRequest extends Request {}

// Transformer 必须继承
use App\Ship\Parents\Transformers\Transformer;
class VoucherTransformer extends Transformer {}

// Repository 必须继承
use App\Ship\Parents\Repositories\Repository;
class VoucherRepository extends Repository {}
```

### 2.5 脚手架命令

```bash
# 创建 Container
php artisan apiato:generate:container

# 创建各组件
php artisan apiato:generate:action
php artisan apiato:generate:task
php artisan apiato:generate:model
php artisan apiato:generate:controller
php artisan apiato:generate:request
php artisan apiato:generate:transformer
php artisan apiato:generate:repository
php artisan apiato:generate:migration
```

### 2.6 命名规范

- Action：`{动词}{名词}Action`，如 `CreateVoucherAction`、`PostVoucherAction`
- Task：`{动词}{名词}Task`，如 `FindVoucherByIdTask`、`UpdateBalanceTask`
- Event：`{名词}{动词}Event`，如 `VoucherPostedEvent`
- Container 名：PascalCase，如 `Voucher`、`AccountsReceivable`

### 2.7 Section 间通信规则

- **同 Section 内**：Container 可以直接依赖其他 Container 的 Task
- **跨 Section**：只能通过 Event/Command，禁止直接调用
- **禁止**：跨 Container 调用 Repository

---

## 三、项目 Container 划分

### Section：Finance

```
app/Containers/Finance/
├── Foundation/          # 科目体系、辅助核算、会计期间
├── Auth/                # 账套管理、用户认证、角色权限
├── Voucher/             # 凭证填制、审核、过账、红冲
├── GeneralLedger/       # 总账、账簿查询、期末结账
├── Report/              # 三大财务报表
├── AccountsReceivable/  # 应收管理
├── AccountsPayable/     # 应付管理
├── Inventory/           # 存货管理
├── Purchase/            # 采购管理
├── Sales/               # 销售管理
├── FixedAsset/          # 固定资产
└── Payroll/             # 工资薪酬
```

---

## 四、多账套设计

### 4.1 隔离方式

单一数据库，所有业务表含 `company_id` 字段，通过 Eloquent GlobalScope 自动注入，业务代码无需手动加 where。

在 Ship 层实现共享 Trait：

```php
// app/Ship/Traits/BelongsToCompany.php
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());
        static::creating(function ($model) {
            $model->company_id ??= app('current.company_id');
        });
    }
}

// 所有账套内 Model 必须 use 此 Trait
class Voucher extends Model
{
    use BelongsToCompany;
}
```

Migration 必须包含：

```php
$table->unsignedBigInteger('company_id')->index();
```

### 4.2 登录流程

1. `POST /api/auth/login` → 验证账号密码 → 返回用户信息 + 所属账套列表
2. `POST /api/auth/select-company` → 选择账套 → 签发带 company_id 的 JWT
3. 后续请求经 `SwitchTenantMiddleware` 自动注入 company_id

### 4.3 JWT Payload

```json
{ "sub": 1, "company_id": 3, "role": "accountant" }
```

### 4.4 角色权限

| 角色 | 权限 |
|------|------|
| admin | 所有操作 |
| accountant | 凭证填制/审核，业务单据 |
| auditor | 只读 + 审核 |
| viewer | 只读 |

---

## 五、数据库表结构

> 所有业务表含 `company_id`（GlobalScope 自动注入）
> 金额字段统一 `decimal(15,2)`，**禁止 float/double**

### 5.1 全局表（不含 company_id）

**companies · 账套**
```
id, code varchar(20) unique, name varchar(100),
fiscal_year_start tinyint default 1,
status enum(active/suspended), created_at
```

**users · 全局用户**
```
id, name varchar(50), email varchar(100) unique, password, created_at
```

**user_company_roles · 用户-账套-角色**
```
id, user_id FK, company_id FK,
role enum(admin/accountant/auditor/viewer), is_active boolean
```

### 5.2 Foundation Container 表

**accounts · 会计科目**
```
id, company_id,
code varchar(20),        -- 4位一级科目（新会计准则），最多4级
name varchar(100),
parent_id bigint,
level tinyint,
element_type enum(asset/liability/equity/income/expense/cost),
balance_direction enum(debit/credit),
is_detail boolean,       -- 只有末级科目(true)才能录凭证
is_active boolean,
has_aux boolean,
INDEX(company_id, code)
```

**aux_categories · 辅助核算类别**
```
id, company_id,
code varchar(20),        -- customer/supplier/dept/employee/inventory/project
name varchar(50),
is_system boolean        -- 系统内置不可删
```

**aux_items · 辅助核算项目**
```
id, company_id, aux_category_id FK,
code varchar(50), name varchar(100),
parent_id bigint,        -- 支持层级
is_active boolean,
extra jsonb,             -- 扩展字段（客户:联系人；员工:部门/职位）
INDEX(company_id, aux_category_id, code)
```

**account_aux_categories · 科目挂载辅助核算**
```
account_id FK, aux_category_id FK,
is_required boolean, sort_order tinyint,
PRIMARY KEY(account_id, aux_category_id)
```

**periods · 会计期间**
```
id, company_id,
fiscal_year smallint, period_number tinyint(1-12),
start_date date, end_date date,
status enum(open/closed/locked),
closed_at timestamp,
INDEX(company_id, fiscal_year, period_number)
```

### 5.3 Voucher Container 表

**vouchers · 凭证主表**
```
id, company_id, period_id FK,
voucher_type enum(receipt/payment/transfer),
voucher_no varchar(20),  -- 格式：2024-记-0001（账套+年份+类型+序号）
voucher_date date,
status enum(draft/reviewed/posted/reversed/voided),
summary varchar(200),
total_debit decimal(15,2), total_credit decimal(15,2),
source_type varchar(50), -- manual/purchase/sales/payroll/depreciation
source_id bigint,        -- 来源单据ID（自动生成凭证时填写）
created_by FK, reviewed_by FK, posted_by FK, posted_at timestamp,
INDEX(company_id, period_id, status),
INDEX(company_id, voucher_date)
```

**voucher_lines · 凭证行**
```
id, company_id, voucher_id FK,
line_no tinyint, account_id FK,
summary varchar(200),
debit decimal(15,2), credit decimal(15,2)
```

**voucher_line_aux · 凭证行辅助核算**
```
id, voucher_line_id FK, aux_category_id FK, aux_item_id FK
```

**balances · 科目余额**
```
id, company_id, period_id FK, account_id FK,
opening_debit decimal(15,2), opening_credit decimal(15,2),
period_debit decimal(15,2), period_credit decimal(15,2),
closing_debit decimal(15,2), closing_credit decimal(15,2),
UNIQUE(company_id, period_id, account_id)
```

**balance_aux · 辅助核算余额（AR 余额权威来源）**
```
id, company_id, period_id FK, account_id FK,
aux_category_id FK, aux_item_id FK,
opening_debit decimal(15,2), opening_credit decimal(15,2),
period_debit decimal(15,2), period_credit decimal(15,2),
closing_debit decimal(15,2), closing_credit decimal(15,2),
UNIQUE(company_id, period_id, account_id, aux_category_id, aux_item_id)
```

### 5.4 AccountsReceivable / AccountsPayable Container 表

**ar_bills · 应收单据**
```
id, company_id, period_id FK,
bill_no varchar(30), bill_date date,
customer_id FK,          -- aux_items.id（customer类型）
amount decimal(15,2), settled_amount decimal(15,2), balance decimal(15,2),
status enum(open/partial/settled/voided),
source_type varchar(50), source_id bigint,
INDEX(company_id, customer_id, status)
```

**ar_receipts · 收款单**
```
id, company_id, period_id FK,
receipt_no varchar(30), receipt_date date, customer_id FK,
amount decimal(15,2), settled_amount decimal(15,2), balance decimal(15,2),
status enum(open/partial/settled)
```

**ar_settlements · 核销记录**
```
id, company_id, ar_bill_id FK, ar_receipt_id FK,
amount decimal(15,2), settled_at timestamp, settled_by FK
```

> ap_bills / ap_payments / ap_settlements 结构同上
> supplier_id 替代 customer_id
> ap_bills 额外含 is_estimate boolean（暂估标记）

### 5.5 Inventory Container 表

**inventories · 库存台账**
```
id, company_id,
inventory_id FK,         -- aux_items（inventory类型）
warehouse_id FK,
qty decimal(15,4), unit_cost decimal(15,4), total_cost decimal(15,2)
```

**inventory_transactions · 出入库流水（FIFO 成本层）**
```
id, company_id,
trans_type enum(purchase_in/sales_out/transfer/adjust),
inventory_id FK, warehouse_id FK,
qty decimal(15,4),       -- 正数入库，负数出库
unit_cost decimal(15,4), total_cost decimal(15,2),
source_type varchar(50), source_id bigint, trans_date date
```

> purchase_orders / purchase_receipts / purchase_invoices（采购）
> sales_orders / sales_shipments / sales_invoices（销售）
> 含标准单据字段：单号/日期/状态/金额/关联单据/审核信息

### 5.6 FixedAsset Container 表

**fixed_assets · 固定资产台账**
```
id, company_id,
asset_no varchar(30), name varchar(100), category varchar(50),
purchase_date date, original_value decimal(15,2),
accumulated_depreciation decimal(15,2), net_value decimal(15,2),
depreciation_method enum(straight_line/double_declining),
useful_life_months int, residual_rate decimal(5,4),
status enum(active/disposed)
```

**depreciation_schedules · 折旧计划**
```
id, company_id, fixed_asset_id FK, period_id FK,
depreciation_amount decimal(15,2), is_posted boolean
```

### 5.7 Payroll Container 表

```
payroll_items    -- 工资项目（基本工资/津贴/社保/个税等）
payrolls         -- 工资单（按期间，含审核状态）
payroll_lines    -- 工资明细（每人每期，含应发/扣减/实发）
```

---

## 六、核心业务流程

### 6.1 凭证状态机

```
draft(草稿) → reviewed(已审核) → posted(已记账)
                               ↘ reversed(已红冲)
             ↘ voided(已作废，记账前可作废)
```

**PostVoucherAction 执行步骤（全部在 DB::transaction 内）：**
1. `CheckPeriodStatusTask` → 期间 locked 则拒绝
2. `ValidateVoucherBalanceTask` → 校验 sum(debit) === sum(credit)
3. `ValidateDetailAccountTask` → 校验末级科目
4. `UpdateBalanceTask` → 写入 balances
5. `UpdateBalanceAuxTask` → 写入 balance_aux
6. `UpdateVoucherStatusTask` → 状态改为 posted
7. 抛出 `VoucherPostedEvent`

**红冲：** 生成金额相反的新凭证，原凭证标记 reversed

### 6.2 期末结账流程

**ClosePeriodAction 执行步骤：**
1. `CheckAllModulesClosedTask` → 检查以下事件是否已收到：
   - InventoryPeriodClosedEvent
   - ArPeriodClosedEvent
   - ApPeriodClosedEvent
   - FixedAssetPeriodClosedEvent
   - PayrollPeriodClosedEvent
2. `CarryForwardProfitLossTask` → 损益结转（收入/费用 → 本年利润）
3. `LockPeriodTask` → 期间状态改为 locked
4. 抛出 `PeriodLockedEvent`（所有 Container 收到后拒绝该期间写操作）
5. `InitNextPeriodOpeningTask` → 初始化下期期初余额

**结账顺序：**
```
存货 → 应收 → 应付 → 固定资产 → 工资 → 总账
```

### 6.3 AR 核销流程（FIFO）

**SettleArAction 执行步骤（全部在 DB::transaction 内）：**
1. `FindArBillsByCustomerTask` → 按日期从早到晚排列未结单据
2. `CalculateSettlementAmountTask` → FIFO 计算本次核销金额
3. `UpdateArBillBalanceTask` → 更新 ar_bill.settled_amount / balance
4. `UpdateArReceiptBalanceTask` → 更新 ar_receipt.settled_amount / balance
5. `CreateArSettlementTask` → 写入 ar_settlements
6. `UpdateBalanceAuxTask` → 更新 balance_aux 对应客户余额

### 6.4 存货成本（FIFO）

- 每次入库创建成本层记录（inventory_transactions）
- 出库时从最早成本层开始消耗
- 出库前必须计算完成 `costAmount` 再触发后续 Event

### 6.5 采购暂估

- 货到票未到：入库时生成暂估应付单（is_estimate=true）
- 下月初：自动红冲暂估单（`ReverseEstimateApBillTask`）
- 收票后：正式录入应付单

---

## 七、跨 Container 事件清单

> Finance Section 内 Container 间通信通过 Event 实现
> 所有 Event 定义在各自 Container 的 Events/ 目录
> Listener 定义在监听方 Container 的 Listeners/ 目录

### 供应链 → 总账（Voucher Container 监听）

| Event | 抛出 Container | 抛出时机 | 生成凭证 |
|-------|--------------|---------|---------|
| PurchaseReceiptPostedEvent | Purchase | 采购入库审核过账 | 借:库存商品 / 贷:应付账款 |
| SalesShipmentPostedEvent | Sales | 销售出库审核过账 | 借:应收账款/贷:主营收入 + 借:主营成本/贷:库存商品 |

### 往来人力 → 总账（Voucher Container 监听）

| Event | 抛出 Container | 抛出时机 | 生成凭证 |
|-------|--------------|---------|---------|
| ArReceiptPostedEvent | AccountsReceivable | 收款单过账 | 借:银行存款 / 贷:应收账款 |
| ApPaymentPostedEvent | AccountsPayable | 付款单过账 | 借:应付账款 / 贷:银行存款 |
| DepreciationCalculatedEvent | FixedAsset | 月末批量折旧完成 | 借:管理费用-折旧 / 贷:累计折旧 |
| PayrollPostedEvent | Payroll | 工资发放确认 | 借:工资费用 / 贷:应付职工薪酬 |

### 供应链 → 往来

| Event | 监听 Container | 动作 |
|-------|--------------|------|
| SalesShipmentPostedEvent | AccountsReceivable | 自动生成应收单据 |
| PurchaseReceiptPostedEvent | AccountsPayable | 自动生成应付单据（或暂估） |

### 结账完成通知（GeneralLedger Container 监听，用于总账结账前置检查）

- InventoryPeriodClosedEvent（Inventory 抛出）
- ArPeriodClosedEvent（AccountsReceivable 抛出）
- ApPeriodClosedEvent（AccountsPayable 抛出）
- FixedAssetPeriodClosedEvent（FixedAsset 抛出）
- PayrollPeriodClosedEvent（Payroll 抛出）

### 总账内部

| Event | 抛出 Container | 监听方 | 动作 |
|-------|--------------|--------|------|
| VoucherPostedEvent | Voucher | GeneralLedger | 更新 balance / balance_aux |
| PeriodLockedEvent | GeneralLedger | 所有 Container | 拒绝该期间写操作 |

---

## 八、财务硬规则

- 金额字段：`decimal(15,2)`，**禁止 float/double**
- 凭证过账后：只能红冲，**禁止修改任何字段**
- 期间 locked 后：所有写操作返回 403
- AR 余额：以 `balance_aux` 为准，不从 ar_bill 汇总
- 存货成本：FIFO
- 凭证借贷平衡：`sum(debit) === sum(credit)`，否则拒绝过账
- 只有末级科目（is_detail=true）才能录凭证

---

## 九、API 设计规范

### 统一响应格式

```json
{
  "data": {},
  "message": "ok",
  "status-code": 200
}
```

Apiato 内置 Fractal Transformer，统一使用 Transformer 格式化输出，禁止在 Controller 直接 return array。

### 路由分组

```
/api/                    # 不需要账套（登录/注册/选账套）
/api/v1/                 # 需要账套（所有业务接口，经过 SwitchTenantMiddleware）
```

---

## 十、账簿与报表需求

### 10.1 账簿

- **科目余额表**：按科目展示期初/本期发生/期末余额，支持多级展开
- **明细账**：按科目+期间查询所有凭证行明细
- **序时账**：按日期排序的所有凭证
- **辅助核算账**：按客户/供应商/部门等查询余额和明细

### 10.2 三大报表

- **资产负债表**：期末时点，资产 = 负债 + 所有者权益（不平衡则报错）
- **利润表**：期间发生额，收入 - 成本 - 费用 = 利润
- **现金流量表**：间接法，需配置科目与现金流量项目映射关系

### 10.3 往来报表

- **账龄分析**：按客户统计未核销余额，分组：未到期 / 逾期30 / 60 / 90 / 90天以上

---

## 十一、前端需求

前端已有样式和部分页面（React，在 `frontend/` 目录）。
**新页面必须复用现有组件和样式，禁止引入新 UI 库。**
API 请求统一封装在 `frontend/src/api/` 对应模块文件。

### 页面清单

**基础设置**
- 登录页 / 选择账套页
- 科目管理（层级树展示，支持新增/编辑/启停）
- 辅助核算类别 + 项目管理
- 会计期间管理（开账/查看状态）
- 用户管理 / 角色权限

**凭证与总账**
- 凭证列表（筛选：期间/类型/状态/关键字）
- 凭证录入（含辅助核算行、实时借贷平衡校验）
- 凭证审核
- 科目余额表 / 明细账 / 序时账 / 辅助核算账
- 期末结账（含前置模块结账状态清单）

**财务报表**
- 资产负债表 / 利润表 / 现金流量表

**供应链**
- 采购订单 / 入库单
- 销售订单 / 出库单
- 库存查询

**往来管理**
- 应收单据 / 收款单 / 核销操作 / 账龄分析
- 应付单据 / 付款单 / 核销操作

**人力资产**
- 固定资产台账 / 折旧计提
- 工资单管理

---

## 十二、MVP 开发优先级

```
P0 · 基础底座（必须最先完成）
  ├─ Apiato 安装配置
  ├─ Auth Container：账套管理、用户认证、选择账套签发JWT
  ├─ SwitchTenantMiddleware + BelongsToCompany GlobalScope
  └─ Foundation Container：科目体系、辅助核算、会计期间

P1 · 凭证核心
  ├─ Voucher Container：填制/审核/过账/红冲
  └─ GeneralLedger Container：科目余额表、明细账

P2 · 业务联动
  ├─ AccountsReceivable + AccountsPayable：单据/收付款/核销
  ├─ Purchase + Sales：采购入库/销售出库（含事件联动）
  └─ Inventory：FIFO 成本核算

P3 · 辅助模块
  ├─ FixedAsset：折旧计提 → 凭证联动
  ├─ Payroll：工资发放 → 凭证联动
  └─ Report：三大报表

P4 · 收尾
  ├─ 期末结账 + 年度结转
  └─ 前端全部页面联调
```

---

## 十三、安装步骤

```bash
# 1. 安装 Apiato
composer create-project apiato/apiato finance
cd finance

# 2. 配置 PostgreSQL
# .env: DB_CONNECTION=pgsql, DB_DATABASE=finance ...

# 3. 运行初始 Migration
php artisan migrate

# 4. 创建 Finance Section 的第一个 Container
php artisan apiato:generate:container
# Section: Finance
# Container: Foundation

# 5. 后续用脚手架命令生成各组件
php artisan apiato:generate:action
php artisan apiato:generate:task
# ...
```
