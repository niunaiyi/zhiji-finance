# Foundation 模块规范

**所属 Agent：** Agent 1
**职责：** 科目体系、辅助核算、会计期间

## 关键设计
- 科目编码：4位一级科目（新会计准则），最多4级
- 只有末级科目（is_detail=true）才能录凭证
- 辅助核算类型内置6种：customer/supplier/dept/employee/inventory/project
- 期间状态流转：open → closed → locked，locked 后禁止任何写操作

## 本模块提供给其他模块的
- `Account` Model：科目信息
- `AuxItem` Model：辅助核算项目（客户/供应商/部门/员工/存货）
- `Period` Model：会计期间
- `BelongsToCompany` Trait：所有账套内 Model 必须 use

## 注意
- `account_aux_categories` 表记录科目挂载了哪些辅助核算类型
- 删除科目前需检查是否已有凭证引用
- 期间 locked 后，其他模块收到 `PeriodLockedEvent` 应拒绝该期间写操作
