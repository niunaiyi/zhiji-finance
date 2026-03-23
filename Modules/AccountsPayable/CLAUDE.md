# AccountsPayable 模块规范

**所属 Agent：** Agent 4
**职责：** 应付单据、付款、核销

## 核心规则
- 核销逻辑同 AR：FIFO
- 付款单过账后抛 `ApPaymentPostedEvent`
- 与采购模块联动：监听 `PurchaseReceiptPostedEvent` 自动生成应付单据

## 暂估处理
采购货到票未到时：
1. 入库时生成暂估应付单（标记 is_estimate=true）
2. 下月初自动红冲暂估单
3. 收到发票后正式录入应付单
