# Payroll 模块规范

**所属 Agent：** Agent 4
**职责：** 工资项目配置、工资计算、发放确认

## 工资项目类型
- 应发项：基本工资/岗位津贴/绩效奖金/加班费
- 扣减项：个人社保/个人公积金/个人所得税
- 实发 = 应发合计 - 扣减合计

## 月末发放（PostPayrollAction）
1. 检查工资单状态（必须已审核）
2. 按部门汇总工资金额
3. 抛出 `PayrollPostedEvent`（携带部门汇总明细）
   - Voucher 模块监听 → 生成凭证
4. 抛出 `PayrollPeriodClosedEvent`

## 与 Foundation 模块关系
员工档案（aux_items 中 category=employee）由 Foundation 管理，本模块只读
部门信息（aux_items 中 category=dept）同上
