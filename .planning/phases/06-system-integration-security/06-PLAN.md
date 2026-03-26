# Phase 6: 系统全流程集成与权限校验 - 实施计划 (PLAN)

## 目标 (Objective)
本阶段旨在完成全系统的 RBAC 权限集成、业务模块到财务模块的自动凭证对接，并进行深度的安全性与集成验证。

---

## 实施任务 (Tasks)

### Wave 1: 权限体系与安全性 (Security & RBAC)

#### Task 1.1: RBAC 角色中间件与 Action 拦截
- **描述**: 在 `Ship` 层实现统一的角色校验逻辑，确保非授权角色无法调用敏感 Action。
- **文件**:
    - `app/Ship/Parents/Actions/Action.php`
    - `app/Containers/Finance/Auth/Middlewares/CheckRoleMiddleware.php`
- **操作**:
    - 修改 `Action` 父类，增加 `verifyRole($allowedRoles)` 方法。
    - 在各模块关键 Action 中引用该方法进行拦截。
- **验收标准**:
    - 使用 `auditor` 角色登录时，调用 `CreateVoucherAction` 返回 403。
    - 使用 `admin` 角色登录时正常操作。

#### Task 1.2: 账套隔离自动化回归测试
- **描述**: 验证 `company_id` 隔离的物理安全性。
- **文件**: `tests/Functional/TenantSecurityTest.php`
- **操作**:
    - 创建两个租户进行交叉请求测试。
- **验收标准**:
    - 租户 A 的 Token 访问租户 B 的接口资源应返回 404 或 403。

### Wave 2: 业务到总账的凭证自动化 (Voucher Integration)

#### Task 2.1: 存货/供应链凭证生成监听器
- **描述**: 实施 `PurchaseReceiptPostedEvent` 和 `SalesShipmentPostedEvent` 的监听，自动生成待审核凭证。
- **文件**:
    - `app/Containers/Finance/Voucher/Listeners/GenerateVoucherFromSupplyChain.php`
- **操作**:
    - 根据业务单据金额自动通过映射关系生成借贷分录。
- **验收标准**:
    - 审核通过一张采购入库单后，`vouchers` 表中自动出现对应的“记账凭证”。

#### Task 2.2: 资产与薪酬凭证集成
- **描述**: 将折旧计提与工资发放流水一键转凭证。
- **文件**:
    - `app/Containers/Finance/Voucher/Listeners/GenerateVoucherFromSpecializedModules.php`
- **验收标准**:
    - 每月折旧计提完成后，自动生成“借：管理费用-折旧，贷：累计折旧”。

### Wave 3: 集成联调与 UI 优化 (UI/UX & Integration)

#### Task 3.1: 最终集成测试与前端 Polish
- **描述**: 验证所有 TODO 项的消除，特别是报表页面的数据实时性。
- **文件**: `frontend/src/pages/**/*.tsx`
- **验收标准**:
    - “科目余额表”能实时反映由业务单据生成的最新凭证数据。

---

## 验证计划 (Verification Plan)

### 自动化测试
- 运行 `php artisan test --filter TenantSecurityTest`
- 运行 `php artisan test --filter VoucherIntegrationTest`

### 手动验证
1. 使用 `accountant` 角色录入采购单并审核。
2. 验证凭证列表是否自动生成了该笔入库的会计分录。
3. 使用 `viewer` 角色尝试删除凭证，验证拦截是否生效。
