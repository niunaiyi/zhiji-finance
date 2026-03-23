# Voucher 模块规范

**所属 Agent：** Agent 2
**职责：** 凭证填制、审核、过账、红冲、自动凭证生成

## 凭证状态机
```
draft → reviewed → posted → reversed
                 ↘ voided（审核前可作废）
```
- posted 后：禁止修改任何字段，只能红冲
- 红冲：生成一张金额相反的新凭证，原凭证标记 reversed

## 过账规则（PostVoucherAction）
1. 检查期间状态（locked 则拒绝）
2. 校验借贷平衡：`sum(debit) === sum(credit)`
3. 校验末级科目
4. 写入 balance（科目余额）
5. 写入 balance_aux（辅助核算余额）
6. 更新凭证状态为 posted
7. 抛出 `VoucherPostedEvent`
以上全部在 `DB::transaction` 内完成

## 自动凭证（监听其他模块事件）
在 `Listeners/` 目录实现，监听以下事件并生成对应凭证：
- `PurchaseReceiptPostedEvent` → 采购入库凭证
- `SalesShipmentPostedEvent` → 销售出库凭证（两笔）
- `ArReceiptPostedEvent` → 收款凭证
- `ApPaymentPostedEvent` → 付款凭证
- `DepreciationCalculatedEvent` → 折旧凭证
- `PayrollPostedEvent` → 工资凭证

## 凭证号规则
格式：`{年份}-{凭证类型}-{4位序号}`，如 `2024-记-0001`
每个账套、每个期间、每个凭证类型独立编号
