# Agent 3 · 供应链 · 进度

## 负责模块
- Modules/Inventory
- Modules/Purchase
- Modules/Sales

## 前置条件
> 确认 docs/agent-1-progress.md 已标记"已就绪"再开始

## 状态

### Modules/Inventory
- [ ] 仓库档案
- [ ] 入库操作（FIFO 成本层写入）
- [ ] 出库操作（FIFO 成本计算）
- [ ] 库存余额查询
- [ ] 月末结账 → 抛 InventoryPeriodClosedEvent

### Modules/Purchase
- [ ] 采购订单 CRUD
- [ ] 采购入库单（关联订单）
- [ ] 入库审核过账 → 抛 PurchaseReceiptPostedEvent
- [ ] 采购发票
- [ ] 暂估处理（货到票未到）
- [ ] 下月初自动红冲暂估 → 抛 PurchaseInvoiceEstimateReversedEvent

### Modules/Sales
- [ ] 销售订单 CRUD
- [ ] 销售出库单（关联订单）
- [ ] 出库审核过账（含 FIFO 成本计算）→ 抛 SalesShipmentPostedEvent
- [ ] 销售发票
