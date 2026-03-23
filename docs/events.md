# 跨模块事件清单（Events）

> 跨模块通信的唯一合法方式。
> 业务模块只抛事件，Voucher 模块（Agent 2）统一监听并生成凭证。

---

## 事件规范

```php
// 命名：{名词}{动词}Event
// 抛出方：业务模块的 Events/ 目录
// 监听方：Modules/Voucher/Listeners/

PurchaseReceiptPostedEvent::dispatch($receipt, $companyId, $periodId);
```

---

## Agent 3 → Agent 2（供应链 → 总账）

### PurchaseReceiptPostedEvent · 采购入库确认
**抛出：** Modules/Purchase，入库单审核过账时
```php
public int     $companyId,
public int     $periodId,
public int     $receiptId,
public int     $supplierId,     // aux_item_id
public array   $lines,          // [{inventory_id, qty, unit_cost, total_cost}]
public float   $totalAmount,
```
**生成凭证：** 借:库存商品 / 贷:应付账款（挂 supplierId）

---

### PurchaseInvoiceEstimateReversedEvent · 暂估红冲
**抛出：** Modules/Purchase，下月初自动红冲
**生成凭证：** 红冲原暂估凭证

---

### SalesShipmentPostedEvent · 销售出库确认
**抛出：** Modules/Sales，出库单审核过账时
```php
public int   $companyId,
public int   $periodId,
public int   $shipmentId,
public int   $customerId,    // aux_item_id
public float $saleAmount,    // 销售金额
public float $costAmount,    // 成本金额（FIFO计算后）
```
**生成凭证（两笔）：**
- 借:应收账款（挂 customerId）/ 贷:主营业务收入
- 借:主营业务成本 / 贷:库存商品

---

### InventoryPeriodClosedEvent · 存货结账完成
**抛出：** Modules/Inventory，月末结账后
**用途：** 总账结账前置检查

---

## Agent 4 → Agent 2（往来人力 → 总账）

### ArReceiptPostedEvent · 应收收款确认
**抛出：** Modules/AccountsReceivable，收款单过账时
```php
public int   $companyId,
public int   $periodId,
public int   $receiptId,
public int   $customerId,
public float $amount,
```
**生成凭证：** 借:银行存款 / 贷:应收账款（挂 customerId）

---

### ApPaymentPostedEvent · 应付付款确认
**抛出：** Modules/AccountsPayable，付款单过账时
**生成凭证：** 借:应付账款（挂 supplierId）/ 贷:银行存款

---

### DepreciationCalculatedEvent · 固定资产折旧计提
**抛出：** Modules/FixedAsset，月末批量计提完成后
```php
public int   $companyId,
public int   $periodId,
public array $lines,   // [{asset_id, dept_id, amount}]
```
**生成凭证：** 借:管理费用-折旧（按部门）/ 贷:累计折旧

---

### PayrollPostedEvent · 工资发放确认
**抛出：** Modules/Payroll，工资单审核发放时
```php
public int   $companyId,
public int   $periodId,
public int   $payrollId,
public float $totalAmount,
public array $deptSummary,  // [{dept_id, amount}]
```
**生成凭证：** 借:工资费用（按部门）/ 贷:应付职工薪酬

---

### 结账完成通知事件（用于总账结账前置检查）
- `InventoryPeriodClosedEvent`
- `ArPeriodClosedEvent`
- `ApPeriodClosedEvent`
- `FixedAssetPeriodClosedEvent`
- `PayrollPeriodClosedEvent`

---

## Agent 2 内部事件

### VoucherPostedEvent · 凭证记账完成
**抛出：** Modules/Voucher
**监听：** GeneralLedger → 更新 balance / balance_aux

### PeriodLockedEvent · 期间锁定
**抛出：** Modules/GeneralLedger，总账结账后
**监听：** 所有模块 → 收到后拒绝该期间的写操作

---

## Agent 3 → Agent 4（供应链 → 往来）

### SalesShipmentPostedEvent
**监听：** AccountsReceivable → 自动生成应收单据

### PurchaseReceiptPostedEvent
**监听：** AccountsPayable → 自动生成应付单据（或暂估）

---

## 总账结账前置检查清单

```php
// Modules/GeneralLedger/Actions/ClosePeriodAction.php
$required = [
    InventoryPeriodClosedEvent::class,
    ArPeriodClosedEvent::class,
    ApPeriodClosedEvent::class,
    FixedAssetPeriodClosedEvent::class,
    PayrollPeriodClosedEvent::class,
];
// 全部收到才允许执行总账结账
```
