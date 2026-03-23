# AccountsReceivable 模块规范

**所属 Agent：** Agent 4
**职责：** 应收单据、收款、核销、账龄分析

## 核心规则
- AR 余额以 `balance_aux` 为准，不从 `ar_bill` 直接汇总
- 核销逻辑：FIFO（按应收单据日期从早到晚核销）
- 收款单过账后抛 `ArReceiptPostedEvent`，由 Voucher 模块生成凭证

## 核销流程（SettleArAction）
1. 选择收款单 + 应收单据
2. FIFO 匹配，计算本次核销金额
3. 更新 `ar_bill.settled_amount` / `ar_bill.balance`
4. 更新 `ar_receipt.settled_amount` / `ar_receipt.balance`
5. 写入 `ar_settlements` 记录
6. 更新 `balance_aux` 对应客户余额
全部在 `DB::transaction` 内完成

## 账龄分析
按客户统计未核销应收余额，分组：
- 未到期
- 逾期 1-30 天
- 逾期 31-60 天
- 逾期 61-90 天
- 逾期 90 天以上

## 与销售模块联动
监听 `SalesShipmentPostedEvent` 自动生成应收单据
