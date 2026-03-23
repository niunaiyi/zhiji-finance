# Agent 4 · 往来 + 人力资产 · 进度

## 负责模块
- Modules/AccountsReceivable
- Modules/AccountsPayable
- Modules/FixedAsset
- Modules/Payroll

## 前置条件
> 确认 docs/agent-1-progress.md 已标记"已就绪"再开始

## 状态

### Modules/AccountsReceivable
- [ ] 应收单据 CRUD
- [ ] 收款单 CRUD
- [ ] 核销（FIFO）
- [ ] 收款过账 → 抛 ArReceiptPostedEvent
- [ ] 账龄分析
- [ ] 监听 SalesShipmentPostedEvent → 自动生成应收单
- [ ] 月末结账 → 抛 ArPeriodClosedEvent

### Modules/AccountsPayable
- [ ] 应付单据 CRUD
- [ ] 付款单 CRUD
- [ ] 核销（FIFO）
- [ ] 付款过账 → 抛 ApPaymentPostedEvent
- [ ] 监听 PurchaseReceiptPostedEvent → 自动生成应付单
- [ ] 暂估应付处理
- [ ] 月末结账 → 抛 ApPeriodClosedEvent

### Modules/FixedAsset
- [ ] 固定资产台账 CRUD
- [ ] 月末批量计提折旧 → 抛 DepreciationCalculatedEvent
- [ ] 资产处置
- [ ] 月末结账 → 抛 FixedAssetPeriodClosedEvent

### Modules/Payroll
- [ ] 工资项目配置
- [ ] 工资单生成（按期间）
- [ ] 工资计算
- [ ] 工资发放确认 → 抛 PayrollPostedEvent
- [ ] 月末结账 → 抛 PayrollPeriodClosedEvent
