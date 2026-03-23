# Agent 2 · 总账核心 · 进度

## 负责模块
- Modules/Voucher
- Modules/GeneralLedger
- Modules/Report

## 前置条件
> 确认 docs/agent-1-progress.md 已标记"已就绪"再开始

## 状态

### Modules/Voucher
- [ ] 凭证填制（含辅助核算行）
- [ ] 凭证审核
- [ ] 凭证过账（更新 balance / balance_aux）
- [ ] 凭证红冲
- [ ] 凭证作废
- [ ] 自动凭证生成（监听 Events）
  - [ ] PurchaseReceiptPostedEvent → 采购入库凭证
  - [ ] SalesShipmentPostedEvent → 销售出库凭证
  - [ ] ArReceiptPostedEvent → 收款凭证
  - [ ] ApPaymentPostedEvent → 付款凭证
  - [ ] DepreciationCalculatedEvent → 折旧凭证
  - [ ] PayrollPostedEvent → 工资凭证

### Modules/GeneralLedger
- [ ] 科目余额表
- [ ] 明细账查询
- [ ] 序时账查询
- [ ] 辅助核算账查询
- [ ] 期末结账（含前置检查清单）
- [ ] 损益结转
- [ ] 年度结转
- [ ] 抛出 PeriodLockedEvent

### Modules/Report
- [ ] 资产负债表
- [ ] 利润表
- [ ] 现金流量表（间接法）
