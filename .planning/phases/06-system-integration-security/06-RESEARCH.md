# Phase 6: 系统全流程集成与权限校验 - 技术研究 (RESEARCH)

## 研究目标
本文档旨在明确“智积财务管理系统”在完成各功能模块开发后，如何进行全流程集成优化、权限体系验证以及自动化凭证生成的具体实施方案。

## 1. 权限体系验证 (RBAC Verification)
### 现状
- `Auth` 容器中已定义基础角色：`admin`, `accountant`, `auditor`, `viewer`。
- 目前大部分接口仅校验登录状态，未严格区分 Action 级的权限。

### 实施方案
- **中间件校验**: 在 `SwitchTenantMiddleware` 后增加角色校验逻辑。
- **Action 级控制**: 在 `Ship/Parents/Actions/Action.php` 或具体 Action 中调用 `checkPermission`。
- **各模块角色权责**:
    - `admin`: 拥有所有权限。
    - `accountant`: 填制凭证、业务录入、审核。
    - `auditor`: 审核凭证、查看账簿、报表。
    - `viewer`: 仅查看。

## 2. 存货/资产/薪酬自动生成凭证 (Voucher Integration)
### 技术挑战
- 业务单据（入库单、折旧计划、工资单）如何映射为标准的财务分录。
- 如何在不破坏 Section 隔离的前提下触发凭证生成。

### 方案设计
- **事件驱动**: 业务模块（如 Inventory）抛出 `InventoryPostedEvent`。
- **监听者 (Voucher Container)**: 在 `Voucher` 容器中实现 `AutoGenerateVoucherListener`。
- **映射模板**: 定义业务类型到科目编码的映射表（如：`inventory_in` -> `借:库存商品(1403), 贷:应付账款(2202)`）。

## 3. 账套隔离深度测试 (Multi-tenant Deep Validation)
- **校验点**: 所有 Model 的 `BelongsToCompany` Trait 是否全覆盖。
- **测试用例**: 创建两个测试租户 A 和 B，尝试通过租户 A 的 ID 读取租户 B 的凭证，确保返回 404/403。

## 4. 审计日志 (Audit Trail)
- **核心逻辑**: 记录关键财务变动操作（填制凭证、作废、结账）。
- **技术选型**: 使用 Laravel 的 `Spatie/laravel-activitylog` 或简单的 `VoucherAuditLogTask`。

---
*Phase: 06-system-integration-security*
*Researcher: Antigravity*
*Date: 2026-03-25*
