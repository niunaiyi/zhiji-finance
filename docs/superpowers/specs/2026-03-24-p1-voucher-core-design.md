# P1 凭证核心模块设计方案

## 概述

**目标：** 实现财务系统的核心凭证管理功能，包括凭证填制、审核、过账、红冲，以及总账查询功能（科目余额表、明细账、序时账）。

**范围：**
- Voucher Container：凭证 CRUD、状态流转、业务规则校验
- GeneralLedger Container：余额计算、账簿查询
- 前端页面：凭证管理、总账查询
- 事件系统：VoucherPostedEvent 触发余额更新

**技术栈：**
- 后端：Apiato v13 (Laravel 11) + Porto 架构
- 前端：React + TypeScript + Ant Design
- 数据库：PostgreSQL 15（表已在 P0 创建）

---

## 架构设计

### 1. Container 划分

```
app/Containers/Finance/
├── Voucher/              # 凭证管理
│   ├── Actions/          # 业务操作
│   ├── Tasks/            # 原子任务
│   ├── Models/           # Voucher, VoucherLine, VoucherLineAux
│   ├── Events/           # VoucherPostedEvent
│   └── UI/API/           # REST API
└── GeneralLedger/        # 总账查询
    ├── Actions/          # 查询操作
    ├── Tasks/            # 余额计算任务
    ├── Models/           # Balance, BalanceAux
    ├── Listeners/        # 监听 VoucherPostedEvent
    └── UI/API/           # REST API
```

### 2. 凭证状态机

```
draft (草稿)
  ↓ review
reviewed (已审核)
  ↓ post                    ↓ void
posted (已记账) --------→ voided (已作废)
  ↓ reverse
reversed (已红冲)
```

**状态转换规则：**
- draft → reviewed：审核通过
- reviewed → posted：过账（不可逆，触发余额更新）
- reviewed → voided：作废（记账前可作废）
- posted → reversed：红冲（生成反向凭证）

### 3. 数据流

```
凭证填制 → 凭证审核 → 凭证过账 → 触发事件 → 更新余额
   ↓           ↓           ↓            ↓           ↓
 draft     reviewed     posted   VoucherPostedEvent  balances
                                                    balance_aux
```

---

## 后端设计

### Voucher Container

#### Models

**Voucher.php**
```php
- id, company_id, period_id
- voucher_type (receipt/payment/transfer)
- voucher_no (格式：2024-记-0001)
- voucher_date, status
- summary, total_debit, total_credit
- source_type, source_id
- created_by, reviewed_by, posted_by, posted_at
- Relationships: lines(), period(), creator()
- Scopes: byPeriod(), byStatus(), byType()
```

**VoucherLine.php**
```php
- id, company_id, voucher_id
- line_no, account_id
- summary, debit, credit
- Relationships: voucher(), account(), auxItems()
```

**VoucherLineAux.php**
```php
- id, voucher_line_id
- aux_category_id, aux_item_id
- Relationships: line(), category(), item()
```

#### Actions

**CreateVoucherAction**
- 输入：CreateVoucherDTO (period_id, voucher_type, voucher_date, summary, lines[])
- 校验：期间状态、借贷平衡、末级科目
- 输出：Voucher 实体
- 事务：DB::transaction

**ReviewVoucherAction**
- 输入：voucher_id, reviewer_id
- 校验：状态必须是 draft
- 更新：status = reviewed, reviewed_by, reviewed_at
- 输出：Voucher 实体

**PostVoucherAction**
- 输入：voucher_id, poster_id
- 校验：
  - 状态必须是 reviewed
  - 期间未锁定
  - 借贷平衡
  - 末级科目
- 执行：
  - 更新 status = posted
  - 调用 UpdateBalanceTask
  - 调用 UpdateBalanceAuxTask
  - 抛出 VoucherPostedEvent
- 输出：Voucher 实体
- 事务：DB::transaction

**ReverseVoucherAction**
- 输入：voucher_id, reverser_id
- 校验：状态必须是 posted
- 执行：
  - 创建反向凭证（金额取反）
  - 原凭证标记 reversed
  - 新凭证自动过账
- 输出：新 Voucher 实体
- 事务：DB::transaction

**VoidVoucherAction**
- 输入：voucher_id
- 校验：状态必须是 draft 或 reviewed
- 更新：status = voided
- 输出：Voucher 实体

**ListVouchersAction**
- 输入：ListVouchersDTO (period_id?, status?, type?, keyword?, page, per_page)
- 查询：分页查询，支持筛选
- 输出：Paginated<Voucher>

**GetVoucherAction**
- 输入：voucher_id
- 查询：包含 lines 和 auxItems
- 输出：Voucher 实体

#### Tasks

**GenerateVoucherNoTask**
- 输入：company_id, period_id, voucher_type
- 逻辑：查询当前期间最大序号 + 1
- 格式：{year}-{type_code}-{序号4位}
- 输出：voucher_no 字符串

**ValidateVoucherBalanceTask**
- 输入：lines[]
- 校验：sum(debit) === sum(credit)
- 输出：boolean

**ValidateDetailAccountTask**
- 输入：account_ids[]
- 校验：所有科目 is_detail = true
- 输出：boolean

**CheckPeriodStatusTask**
- 输入：period_id
- 校验：status = open
- 输出：boolean

**UpdateBalanceTask**
- 输入：period_id, account_id, debit, credit
- 逻辑：
  - 查找或创建 balance 记录
  - 更新 period_debit, period_credit
  - 重算 closing_debit, closing_credit
- 输出：Balance 实体

**UpdateBalanceAuxTask**
- 输入：period_id, account_id, aux_category_id, aux_item_id, debit, credit
- 逻辑：同 UpdateBalanceTask
- 输出：BalanceAux 实体

#### Events

**VoucherPostedEvent**
```php
public function __construct(
    public int $voucherId,
    public int $companyId,
    public int $periodId,
    public array $lines  // [{account_id, debit, credit, aux_items[]}]
) {}
```

#### API Endpoints

```
POST   /api/v1/vouchers              # 创建凭证
GET    /api/v1/vouchers              # 列表查询
GET    /api/v1/vouchers/{id}         # 详情查询
PUT    /api/v1/vouchers/{id}         # 更新凭证（仅 draft 状态）
POST   /api/v1/vouchers/{id}/review  # 审核凭证
POST   /api/v1/vouchers/{id}/post    # 过账凭证
POST   /api/v1/vouchers/{id}/reverse # 红冲凭证
POST   /api/v1/vouchers/{id}/void    # 作废凭证
```

### GeneralLedger Container

#### Models

**Balance.php**
```php
- id, company_id, period_id, account_id
- opening_debit, opening_credit
- period_debit, period_credit
- closing_debit, closing_credit
- Relationships: period(), account()
- Scopes: byPeriod(), byAccount()
```

**BalanceAux.php**
```php
- id, company_id, period_id, account_id
- aux_category_id, aux_item_id
- opening_debit, opening_credit
- period_debit, period_credit
- closing_debit, closing_credit
- Relationships: period(), account(), category(), item()
```

#### Actions

**GetBalanceSheetAction**
- 输入：company_id, period_id, account_id?
- 查询：科目余额表（支持层级展开）
- 输出：Balance[] with hierarchy

**GetDetailLedgerAction**
- 输入：company_id, period_id, account_id
- 查询：明细账（凭证行明细）
- 输出：VoucherLine[] with voucher info

**GetChronologicalLedgerAction**
- 输入：company_id, period_id, start_date?, end_date?
- 查询：序时账（按日期排序的凭证）
- 输出：Voucher[] ordered by voucher_date

**GetAuxiliaryLedgerAction**
- 输入：company_id, period_id, aux_category_id, aux_item_id
- 查询：辅助核算账（按辅助项查询余额和明细）
- 输出：BalanceAux[] with details

#### Listeners

**UpdateBalanceOnVoucherPosted**
- 监听：VoucherPostedEvent
- 执行：
  - 遍历 lines
  - 调用 UpdateBalanceTask
  - 如有 aux_items，调用 UpdateBalanceAuxTask

#### API Endpoints

```
GET /api/v1/general-ledger/balance-sheet       # 科目余额表
GET /api/v1/general-ledger/detail-ledger       # 明细账
GET /api/v1/general-ledger/chronological       # 序时账
GET /api/v1/general-ledger/auxiliary-ledger    # 辅助核算账
```

---

## 前端设计

### 页面结构

```
frontend/src/pages/
├── Vouchers/
│   ├── VoucherList.tsx           # 凭证列表
│   ├── VoucherForm.tsx           # 凭证填制/编辑
│   ├── VoucherDetail.tsx         # 凭证详情
│   └── VoucherReview.tsx         # 凭证审核
└── GeneralLedger/
    ├── BalanceSheet.tsx          # 科目余额表
    ├── DetailLedger.tsx          # 明细账
    ├── ChronologicalLedger.tsx   # 序时账
    └── AuxiliaryLedger.tsx       # 辅助核算账
```

### 核心组件

#### VoucherForm.tsx

**功能：**
- 凭证头信息录入（期间、类型、日期、摘要）
- 凭证行录入（科目、摘要、借方、贷方、辅助核算）
- 实时借贷平衡校验
- 支持添加/删除行
- 支持辅助核算项选择

**状态管理：**
```typescript
interface VoucherFormState {
  header: {
    period_id: number;
    voucher_type: 'receipt' | 'payment' | 'transfer';
    voucher_date: string;
    summary: string;
  };
  lines: VoucherLine[];
  totalDebit: number;
  totalCredit: number;
  isBalanced: boolean;
}
```

**校验规则：**
- 至少 2 行
- 借贷必须平衡
- 科目必须是末级科目
- 如科目启用辅助核算，必须选择辅助项

#### VoucherList.tsx

**功能：**
- 列表展示（期间、凭证号、日期、摘要、金额、状态）
- 筛选（期间、类型、状态、关键字）
- 分页
- 操作按钮（查看、编辑、审核、过账、红冲、作废）

**权限控制：**
- draft：可编辑、可审核、可作废
- reviewed：可过账、可作废
- posted：可红冲、可查看
- reversed/voided：仅可查看

#### BalanceSheet.tsx

**功能：**
- 树形展示科目余额
- 支持展开/折叠
- 显示期初、本期发生、期末余额
- 支持导出 Excel

**数据结构：**
```typescript
interface BalanceNode {
  account: Account;
  opening_debit: number;
  opening_credit: number;
  period_debit: number;
  period_credit: number;
  closing_debit: number;
  closing_credit: number;
  children?: BalanceNode[];
}
```

### API Services

```typescript
// frontend/src/api/vouchers.ts
export const vouchersApi = {
  list: (params: ListVouchersParams) => Promise<PaginatedResponse<Voucher>>,
  get: (id: number) => Promise<Voucher>,
  create: (data: CreateVoucherRequest) => Promise<Voucher>,
  update: (id: number, data: UpdateVoucherRequest) => Promise<Voucher>,
  review: (id: number) => Promise<Voucher>,
  post: (id: number) => Promise<Voucher>,
  reverse: (id: number) => Promise<Voucher>,
  void: (id: number) => Promise<Voucher>,
};

// frontend/src/api/generalLedger.ts
export const generalLedgerApi = {
  balanceSheet: (params: BalanceSheetParams) => Promise<Balance[]>,
  detailLedger: (params: DetailLedgerParams) => Promise<VoucherLine[]>,
  chronological: (params: ChronologicalParams) => Promise<Voucher[]>,
  auxiliaryLedger: (params: AuxiliaryParams) => Promise<BalanceAux[]>,
};
```

---

## 业务规则实现

### 1. 借贷平衡校验

**位置：** ValidateVoucherBalanceTask
```php
public function run(array $lines): bool
{
    $totalDebit = collect($lines)->sum('debit');
    $totalCredit = collect($lines)->sum('credit');

    return bccomp($totalDebit, $totalCredit, 2) === 0;
}
```

### 2. 末级科目校验

**位置：** ValidateDetailAccountTask
```php
public function run(array $accountIds): bool
{
    $nonDetailAccounts = Account::whereIn('id', $accountIds)
        ->where('is_detail', false)
        ->exists();

    return !$nonDetailAccounts;
}
```

### 3. 期间锁定检查

**位置：** CheckPeriodStatusTask
```php
public function run(int $periodId): bool
{
    $period = Period::find($periodId);

    if (!$period || $period->status === 'locked') {
        throw new PeriodLockedException();
    }

    return true;
}
```

### 4. 余额更新逻辑

**位置：** UpdateBalanceTask
```php
public function run(int $periodId, int $accountId, string $debit, string $credit): Balance
{
    $balance = Balance::firstOrCreate(
        ['company_id' => app('current.company_id'), 'period_id' => $periodId, 'account_id' => $accountId],
        ['opening_debit' => '0.00', 'opening_credit' => '0.00']
    );

    $balance->period_debit = bcadd($balance->period_debit, $debit, 2);
    $balance->period_credit = bcadd($balance->period_credit, $credit, 2);

    // 重算期末余额
    $account = Account::find($accountId);
    if ($account->balance_direction === 'debit') {
        $balance->closing_debit = bcadd(
            bcsub($balance->opening_debit, $balance->opening_credit, 2),
            bcsub($balance->period_debit, $balance->period_credit, 2),
            2
        );
        $balance->closing_credit = '0.00';
    } else {
        $balance->closing_credit = bcadd(
            bcsub($balance->opening_credit, $balance->opening_debit, 2),
            bcsub($balance->period_credit, $balance->period_debit, 2),
            2
        );
        $balance->closing_debit = '0.00';
    }

    $balance->save();
    return $balance;
}
```

### 5. 凭证号生成规则

**位置：** GenerateVoucherNoTask
```php
public function run(int $companyId, int $periodId, string $voucherType): string
{
    $period = Period::find($periodId);
    $year = $period->fiscal_year;

    $typeCode = match($voucherType) {
        'receipt' => '收',
        'payment' => '付',
        'transfer' => '记',
    };

    $maxNo = Voucher::where('company_id', $companyId)
        ->where('period_id', $periodId)
        ->where('voucher_type', $voucherType)
        ->max('voucher_no');

    if ($maxNo) {
        preg_match('/(\d+)$/', $maxNo, $matches);
        $nextSeq = intval($matches[1]) + 1;
    } else {
        $nextSeq = 1;
    }

    return sprintf('%d-%s-%04d', $year, $typeCode, $nextSeq);
}
```

---

## 测试策略

### 后端测试

#### 单元测试

**Tasks 测试：**
- ValidateVoucherBalanceTask：测试借贷平衡校验
- ValidateDetailAccountTask：测试末级科目校验
- GenerateVoucherNoTask：测试凭证号生成
- UpdateBalanceTask：测试余额更新逻辑

**Models 测试：**
- Voucher：测试关系、作用域
- Balance：测试余额计算

#### 功能测试

**Actions 测试：**
- CreateVoucherAction：测试凭证创建流程
- PostVoucherAction：测试过账流程（含事务回滚）
- ReverseVoucherAction：测试红冲流程

**API 测试：**
- 测试完整的 CRUD 流程
- 测试状态转换
- 测试权限控制

#### 集成测试

**事件测试：**
- 测试 VoucherPostedEvent 触发
- 测试 UpdateBalanceOnVoucherPosted 监听器
- 测试余额更新正确性

### 前端测试

#### 组件测试

- VoucherForm：测试表单校验、借贷平衡计算
- VoucherList：测试筛选、分页
- BalanceSheet：测试树形展开、数据展示

#### 集成测试

- 测试完整的凭证填制流程
- 测试凭证审核过账流程
- 测试余额查询功能

---

## 实施顺序

### Phase 1: 后端核心（Voucher Container）
1. Models + Migrations（已在 P0 完成）
2. Tasks（校验、生成凭证号、更新余额）
3. Actions（CRUD、状态转换）
4. Events（VoucherPostedEvent）
5. API Controllers + Routes
6. 单元测试 + 功能测试

### Phase 2: 后端查询（GeneralLedger Container）
1. Models（Balance, BalanceAux）
2. Listeners（UpdateBalanceOnVoucherPosted）
3. Actions（查询操作）
4. API Controllers + Routes
5. 集成测试

### Phase 3: 前端实现
1. API Services（vouchers, generalLedger）
2. Types（TypeScript 接口）
3. 凭证管理页面（List, Form, Detail, Review）
4. 总账查询页面（BalanceSheet, DetailLedger, Chronological, Auxiliary）
5. 组件测试 + 集成测试

### Phase 4: 集成测试与优化
1. 端到端测试
2. 性能优化
3. 错误处理完善
4. 文档完善

---

## 技术决策

### 1. 为什么使用事件驱动更新余额？

**优点：**
- 解耦：Voucher 和 GeneralLedger 职责分离
- 可扩展：未来其他模块也可监听凭证事件
- 可测试：事件和监听器可独立测试

**缺点：**
- 复杂度：需要理解事件系统
- 调试：异步处理可能增加调试难度

**决策：** 采用事件驱动，因为财务系统需要良好的模块化和可扩展性。

### 2. 为什么余额更新在事务内？

**原因：**
- 数据一致性：凭证过账和余额更新必须原子性
- 错误回滚：任何步骤失败都应回滚整个操作

**实现：**
- PostVoucherAction 使用 DB::transaction
- 事件监听器在同一事务内执行

### 3. 为什么使用 bcmath 处理金额？

**原因：**
- 精度：避免浮点数精度问题
- 财务规范：金额计算必须精确到分

**实现：**
- 所有金额字段使用 decimal(15,2)
- 所有金额计算使用 bcadd/bcsub/bccomp

### 4. 前端为什么分离 List 和 Form？

**原因：**
- 复杂度：凭证表单逻辑复杂（多行、辅助核算、实时校验）
- 可维护性：分离后每个组件职责单一
- 性能：避免列表页加载表单逻辑

---

## 风险与缓解

### 风险 1：余额计算错误

**影响：** 严重 - 导致账目不平
**缓解：**
- 完善的单元测试覆盖余额计算逻辑
- 集成测试验证完整流程
- 提供余额重算工具（手动修复）

### 风险 2：并发过账冲突

**影响：** 中等 - 可能导致凭证号重复或余额错误
**缓解：**
- 数据库唯一约束（voucher_no）
- 乐观锁（version 字段）
- 事务隔离级别设置

### 风险 3：前端借贷不平衡提交

**影响：** 低 - 后端会拒绝
**缓解：**
- 前端实时校验
- 后端二次校验
- 清晰的错误提示

### 风险 4：期间锁定后误操作

**影响：** 中等 - 可能导致数据混乱
**缓解：**
- 所有写操作检查期间状态
- 前端禁用已锁定期间的操作按钮
- 审计日志记录所有操作

---

## 成功标准

### 功能完整性
- ✅ 凭证可以填制、审核、过账、红冲、作废
- ✅ 余额自动更新且计算正确
- ✅ 科目余额表、明细账、序时账可查询
- ✅ 辅助核算账可查询

### 数据准确性
- ✅ 借贷必须平衡
- ✅ 余额计算精确到分
- ✅ 期间锁定后无法修改

### 性能要求
- ✅ 凭证列表查询 < 500ms
- ✅ 凭证过账 < 1s
- ✅ 余额表查询 < 1s

### 用户体验
- ✅ 表单校验实时反馈
- ✅ 操作成功/失败有明确提示
- ✅ 加载状态清晰

### 测试覆盖
- ✅ 单元测试覆盖率 > 80%
- ✅ 功能测试覆盖所有 API
- ✅ 集成测试覆盖完整流程
