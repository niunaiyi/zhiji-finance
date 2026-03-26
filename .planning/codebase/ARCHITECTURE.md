# 架构概览 (Architecture Overview)

本文档概述了“智积财务”项目的架构模式和高层设计。

## 核心架构模式：Porto SAP
该项目遵循 **软件架构模式 (Porto)**，通过 **Apiato** 框架实现。该模式强调在 Laravel 开发中采用高度模块化、基于容器的方法。

### 主要层级

1.  **Ship 层 (`app/Ship`)**
    *   包含所有容器共用的基础设施、父类和公共逻辑。
    *   包括基础控制器 (Controllers)、模型 (Models)、Actions 和 Tasks，供特定领域的组件继承。
    *   存放核心中间件（如 `SwitchTenantMiddleware.php`）和提供者 (Providers)。

2.  **Containers 层 (`app/Containers`)**
    *   核心业务逻辑被划分为 **Containers**（领域模块）。
    *   每个容器是一个遵循标准内部结构的自包含单元：
        *   **UI/API**: HTTP 请求的入口点（Controllers, Requests, Routes）。
        *   **Actions**: 执行特定用例的编排者（如 `ClosePeriodAction`）。它们是进入业务逻辑的唯一入口。
        *   **Tasks**: 小型的、可复用的逻辑单元（如 `FindAccountByCodeTask`）。Actions 调用多个 Tasks。
        *   **Models**: Eloquent 模型和数据库迁移。
        *   **Data/Repositories**: 数据访问层（本项目中常与 Models 结合）。
        *   **Mails/Notifications/Events/Listeners**: 领域特定通信。

## 前端架构
前端是位于 `frontend/` 目录下的现代 **React 单页应用 (SPA)**。

*   **构建工具**: Vite。
*   **路由**: React Router。
*   **状态管理**: 使用 React Context API 处理全局状态（如认证信息）。
*   **API 通信**: 使用带有拦截器的 Axios，处理租户请求头 (`X-Company-Id`) 和认证令牌。
*   **样式**: 原生 CSS（遵循项目规范）。

## 关键领域中心 (Key Domain Hubs)
*   **Finance Container**: 财务逻辑的核心引擎（总账、凭证、报表）。
*   **AppSection**: 处理通用应用功能（认证、用户）。
*   **Vendor**: 自定义集成或第三方逻辑封装。

## 数据隔离 (多账套/Multi-tenancy)
系统基于 `company_id` 实现多账套。账套切换由 `SwitchTenantMiddleware.php` 处理，该中间件从传入请求中读取 `X-Company-Id` 请求头。
