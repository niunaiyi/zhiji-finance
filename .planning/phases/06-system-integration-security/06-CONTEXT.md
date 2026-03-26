# Phase 6: 系统全流程集成与权限校验 - 上下文 (CONTEXT)

**日期:** 2026-03-25
**状态:** 准备开始执行

## 1. 业务决策 (Business Decisions)

### 1.1 权限矩阵 (Access Control Matrix)
| 功能模块 | admin | accountant | auditor | viewer |
| :--- | :--- | :--- | :--- | :--- |
| 基础设置 (Foundation) | 全部 | 查看 | 查看 | 查看 |
| 凭证录入 (Voucher) | 全部 | 新增/编辑/提交 | 审核/过账 | 查看 |
| 财务报表 (Reports) | 全部 | 查看 | 查看 | 查看 |
| 业务单据 (Stock/Payroll) | 全部 | 录入/提交 | 审核 | 查看 |

**决策**: 权限校验应在 Action 层统一实施，确保 API 接口对于非法角色的请求返回 403 Forbidden。

### 1.2 凭证集成逻辑 (Voucher Integration)
- **触发源**:
    - 存货入库/出库审核过账。
    - 固定资产月度折旧计提确认。
    - 薪酬发放审核通过。
- **动作**: 自动在 `vouchers` 表中创建一条状态为 `reviewed` 的凭证，并附带正确的借贷分录。
- **决策**: 凭证摘要必须注明来源单据号（如：“自动生成：采购入库单 [RK20240301]”）。

## 2. 技术决策 (Technical Decisions)

### 2.1 账套隔离安全性 (Multi-tenancy)
- **规范**: 严禁在代码中使用 `where('company_id', ...)` 这种手动过滤方式。
- **实现**: 必须确保所有业务 Model 都 `use BelongsToCompany` trait。
- **验证**: 编写集成测试，模拟 A 租户 Token 访问 B 租户资源的非法请求。

### 2.2 审计日志 (Audit Trail)
- **重点**: 记录凭证的“审核人”、“过账人”以及操作时间。
- **实现**: 在 `vouchers` 表中已有相关字段，需确保 Action 中正确填充这些审计信息。

## 3. 已锁定细节 (Locked Decisions)
- API 路由必须统一经过 `auth:api` 和 `tenant` 中间件。
- 金额计算严格使用 `Decimal` 类型，保留 2 位小数。
- 只有“末级科目”允许作为凭证分录的科目。

---
*Phase: 06-system-integration-security*
*Status: Ready for Planning*
