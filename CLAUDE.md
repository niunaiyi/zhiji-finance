# 财务系统 · 全局开发规范

## 必读文档（自动加载）
@docs/schema.md
@docs/events.md

---

## 项目概述
模仿用友 U8 的企业财务管理系统。
- 后端：Laravel 11 + PostgreSQL 15
- 前端：React（在 frontend/ 目录）
- 认证：JWT（tymon/jwt-auth）
- 模块化：nwidart/laravel-modules
- 多账套：单数据库 + company_id GlobalScope 隔离

---

## Agent 文件所有权（禁止越界修改）

| Agent | 负责目录 |
|-------|---------|
| Agent 1 | Modules/Foundation, Modules/Auth, database/migrations/ |
| Agent 2 | Modules/Voucher, Modules/GeneralLedger, Modules/Report |
| Agent 3 | Modules/Inventory, Modules/Purchase, Modules/Sales |
| Agent 4 | Modules/AccountsReceivable, Modules/AccountsPayable, Modules/FixedAsset, Modules/Payroll |
| Agent 5 | frontend/ |

### 跨模块规则
- 需要其他模块的 Model：直接 use，**不修改对方文件**
- 需要其他模块的数据：查 docs/schema.md
- 需要触发其他模块：查 docs/events.md，用已定义的 Event
- **禁止**跨模块调用 Repository

---

## 分层规范

### 目录结构
```
Modules/{ModuleName}/
├── Actions/         # 单一职责业务操作（每个操作一个类）
├── DTO/             # 数据传输对象
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Repositories/
├── Events/          # 本模块抛出的事件
├── Listeners/       # 监听其他模块的事件
├── Routes/
│   └── api.php
└── CLAUDE.md        # 模块专属规范（自动加载）
```

### 分层职责
- **Controller**：接收请求 → 调 Action → 返回响应，禁止写业务逻辑
- **Action**：单一业务操作，跨表操作必须用 `DB::transaction`
- **Repository**：封装所有 Eloquent 查询
- **DTO**：层间传递强类型数据，禁止用裸 array 传参
- **Event/Listener**：跨模块通信的唯一合法方式

---

## 多账套隔离

所有业务表含 `company_id`，通过 GlobalScope 自动注入，业务代码无需手动加 where。

```php
// 所有账套内 Model 必须使用此 Trait
use BelongsToCompany;
```

Migration 必须包含：
```php
$table->unsignedBigInteger('company_id')->index();
```

---

## 财务硬规则

- 金额字段：统一 `decimal(15,2)`，**禁止 float/double**
- 凭证过账后：只能红冲，**禁止任何字段修改**
- 期间锁定后（status = locked）：所有写操作直接拒绝
- AR 余额：以 `balance_aux` 为准
- 存货成本：FIFO 先进先出
- 凭证借贷必须平衡：`sum(debit) === sum(credit)`

---

## 凭证自动生成规则

| 业务操作 | 借方 | 贷方 |
|---------|------|------|
| 采购入库确认 | 库存商品 | 应付账款 |
| 销售出库确认 | 应收账款 | 主营业务收入 |
| 销售出库确认 | 主营业务成本 | 库存商品 |
| 收款核销 | 银行存款 | 应收账款 |
| 付款核销 | 应付账款 | 银行存款 |
| 固定资产折旧 | 管理费用-折旧 | 累计折旧 |
| 工资发放 | 工资费用 | 应付职工薪酬 |

---

## 期末结账顺序

```
存货 → 应收 → 应付 → 固定资产 → 工资 → 总账
```

---

## 代码规范

- Action：`{动词}{名词}Action`，如 `CreateVoucherAction`
- Event：`{名词}{动词}Event`，如 `VoucherPostedEvent`
- DTO：`{动词}{名词}DTO`，如 `CreateVoucherDTO`
- Git commit：`feat(module): 描述` / `fix(module): 描述`

---

## 进度文件
- `docs/agent-1-progress.md`
- `docs/agent-2-progress.md`
- `docs/agent-3-progress.md`
- `docs/agent-4-progress.md`
- `docs/agent-5-progress.md`
