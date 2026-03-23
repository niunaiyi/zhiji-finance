# GeneralLedger 模块规范

**所属 Agent：** Agent 2
**职责：** 账簿查询、期末结账、年度结转

## 账簿类型
- 科目余额表：从 `balance` 表读取，按科目汇总
- 明细账：从 `voucher_line` join `voucher` 查询
- 序时账：按日期排序的所有凭证行
- 辅助核算账：从 `balance_aux` + `voucher_line_aux` 查询

## 期末结账（ClosePeriodAction）
结账前必须检查以下事件是否已收到（所有业务模块已结账）：
- InventoryPeriodClosedEvent
- ArPeriodClosedEvent
- ApPeriodClosedEvent
- FixedAssetPeriodClosedEvent
- PayrollPeriodClosedEvent

全部通过后：
1. 执行损益结转（收入/费用 → 本年利润）
2. 将期间状态改为 locked
3. 抛出 `PeriodLockedEvent`
4. 初始化下一期间的期初余额

## 年度结转
- 将本年利润结转至未分配利润
- 初始化新年度所有期间
- 将年末余额复制为新年度第一期期初余额
