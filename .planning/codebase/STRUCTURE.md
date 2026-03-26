# 代码库结构 (Codebase Structure)

本文档提供了目录结构和关键组件用途的高级映射。

## 项目根目录

| 路径 | 用途 |
| :--- | :--- |
| `app/` | 后端应用逻辑 (Laravel/Apiato)。 |
| `frontend/` | React SPA 源码。 |
| `config/` | Laravel 配置文件。 |
| `database/` | 全局迁移 (OAuth/Passport) 和 Seeders。 |
| `docs/` | 技术文档和进度报告。 |
| `Modules/` | 领域模块的架构规范 (CLAUDE.md)。 |
| `public/` | Web 服务器入口 (`index.php`) 和静态资产。 |
| `routes/` | 全局 Laravel 路由（在 Apiato 中很少使用）。 |
| `tests/` | 全局测试套件（功能、集成、单元）。 |

## 后端: `app/Containers/Finance`
核心业务逻辑位于此处，按财务子模块组织。

| 子模块 | 描述 |
| :--- | :--- |
| `GeneralLedger` | 科目管理、结账和财务报表。 |
| `Voucher` | 凭证（凭证、收款、付款）的录入和管理。 |
| `AccountsPayable` | 供应商发票和付款。 |
| `AccountsReceivable` | 客户发票和收款。 |
| `FixedAsset` | 资产折旧和跟踪。 |

## 后端: `app/Ship`
共享应用代码。

| 目录 | 用途 |
| :--- | :--- |
| `Parents/` | 所有 Porto 组件的基础类 (Actions, Tasks, Models 等)。 |
| `Middleware/` | 全局中间件 (如 `SwitchTenantMiddleware.php`)。 |
| `Exceptions/` | 共享错误处理逻辑。 |

## 前端: `frontend/src`
按标准 React 模式组织。

| 目录 | 用途 |
| :--- | :--- |
| `api/` | API 客户端配置和 Axios 拦截器。 |
| `components/` | 可复用的 UI 组件。 |
| `context/` | React Context 提供者 (Auth, Theme)。 |
| `pages/` | 顶层视图组件 (资产负债表、账簿等)。 |
| `types/` | TypeScript 接口和类型定义。 |
