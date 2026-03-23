# Sales 模块规范

**所属 Agent：** Agent 3
**职责：** 销售订单、出库单、销售发票

## 业务流程
销售订单 → 销售出库单 → 审核过账 → 销售发票

## 审核过账（PostSalesShipmentAction）
1. 调用 Inventory 模块出库逻辑（FIFO 计算成本）
2. 抛出 `SalesShipmentPostedEvent`（携带销售金额 + 成本金额）
   - Voucher 模块监听 → 生成两笔凭证
   - AccountsReceivable 模块监听 → 生成应收单据

## 注意
出库成本金额在抛事件前必须由 Inventory 模块计算完毕
