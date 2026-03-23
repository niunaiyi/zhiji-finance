# Purchase 模块规范

**所属 Agent：** Agent 3
**职责：** 采购订单、入库单、采购发票

## 业务流程
采购订单 → 采购入库单 → 审核过账 → 采购发票

## 审核过账（PostPurchaseReceiptAction）
1. 调用 Inventory 模块入库逻辑（更新库存）
2. 抛出 `PurchaseReceiptPostedEvent`
   - Voucher 模块监听 → 生成凭证（借:库存商品 贷:应付账款）
   - AccountsPayable 模块监听 → 生成应付单据

## 暂估处理
货到票未到：入库单审核时标记暂估，AccountsPayable 模块处理后续红冲逻辑
