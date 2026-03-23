# Inventory 模块规范

**所属 Agent：** Agent 3
**职责：** 存货出入库、库存余额、成本核算

## 成本核算：FIFO
- 每次入库创建一个成本层（qty, unit_cost）
- 出库时从最早的成本层开始消耗
- `inventory_transactions` 记录每次出入库及对应成本

## 出入库类型
- purchase_in：采购入库
- sales_out：销售出库
- transfer：库间调拨
- adjust：盘点调整

## 月末结账（InventoryPeriodCloseAction）
1. 检查所有出入库单是否已审核
2. 计算期末库存余额
3. 抛出 `InventoryPeriodClosedEvent`

## 与其他模块关系
- 存货档案（aux_items 中 category=inventory）由 Foundation 模块管理，本模块只读
- 入库/出库后通知 Voucher 模块生成凭证（通过 Event）
