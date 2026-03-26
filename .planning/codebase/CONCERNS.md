# 代码库关注点与技术债 (Codebase Concerns & Technical Debt)

本文档识别了当前代码库中的跨切关注点、技术债和架构风险。

## 高风险架构关注点

### 1. 硬编码的财务逻辑
在 `app/Containers/Finance/GeneralLedger/UI/API/Controllers/ReportController.php` 中，科目分类（如资产与负债）是由硬编码的字符串前缀（如 `1xxx` 代表资产）决定的。
*   **风险**: 脆弱且缺乏灵活性。一旦会计科目表结构发生变化，报表逻辑将会失效。
*   **建议**: 使用 `element_type` 列或正式的科目组映射系统。

### 2. 核心会计流程不完整
关键操作如 `ClosePeriodAction.php` 和 `YearEndRolloverAction.php` 包含核心逻辑的 `TODO` 存根。
*   **风险**: 系统目前无法执行完整的会计循环结账（如损益结转）。
*   **建议**: 实施子模块状态的事件驱动检查，并完成结转逻辑。

### 3. 租户隔离脆弱性
租户隔离依赖于通过 `X-Company-Id` 请求头和 `SwitchTenantMiddleware.php` 进行的手动管理。
*   **风险**: 潜在的数据泄露。模型中没有观察到全局 Eloquent Scope 来自动按 `company_id` 过滤查询。
*   **建议**: 为所有租户感知模型实现 `TenantTrait` 并包含全局 Scopes，确保在框架层级处理数据隔离。

## 技术债

### 1. 前端集成缺口
多个报表页面（如 `BalanceSheetPage.tsx`, `DetailLedgerPage.tsx`）包含数据加载的 `TODO` 注释。
*   **影响**: 核心报表功能尚未连接到后端。
*   **建议**: 优先实施报表 API 端点和前端 Hooks。

### 2. 测试模式不一致
`AppSection` 中的许多测试包含“移动到 Request 测试”的注释，表明遗留的功能测试与新模式之间尚未完全解决的混合情况。
*   **影响**: 对开发人员造成困惑，并可能导致测试覆盖范围出现缺口。
*   **建议**: 统一使用 Request 测试进行 API 验证，并合并测试套件。

## 架构风险

### 1. 同步与异步处理
繁重的财务操作（如结账或生成复杂报表）似乎是同步处理的。
*   **风险**: 随着数据量的增加，会出现性能瓶颈和请求超时。
*   **建议**: 将运行时间较长的财务流程移至 Laravel 队列 (Queues)。
