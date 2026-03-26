# 编码规范 (Coding Conventions)

**分析日期:** 2025-11-20

## 命名模式

### 后端 (PHP/Laravel/Apiato)

**文件:**
- Actions: `[ActionName]Action.php` (如 `CreateAccountAction.php`)
- Tasks: `[TaskName]Task.php` (如 `CreateAccountTask.php`)
- Models: `[ModelName].php` (如 `Account.php`)
- Controllers: `[ControllerName]Controller.php` (如 `AccountController.php`)
- Requests: `[RequestName]Request.php` (如 `CreateAccountRequest.php`)
- Migrations: `YYYY_MM_DD_HHMMSS_create_[table_name]_table.php`

**函数:**
- Action/Task 入口点: `run()`
- Controller 方法: 标准 Laravel 命名 (如 `index`, `store`, `show`, `update`, `destroy`)
- 辅助函数: `camelCase()`

**变量:**
- 本地变量: `$camelCase`
- 类属性: `$camelCase`（优先使用 private/protected，适用时加 `readonly`）

**类型:**
- 在所有 PHP 8.2+ 代码中，对参数和返回值使用严格类型声明。

### 前端 (TypeScript/React)

**文件:**
- 组件: `PascalCase.tsx` (如 `AuxiliarySelector.tsx`)
- 页面: `PascalCase.tsx` (如 `AccountsPayable.tsx`)
- API 客户端: `camelCase.ts` (如 `accounts.ts`)
- Hooks: `useCamelCase.ts` (如 `useAuth.ts`)

**函数:**
- 组件函数: `PascalCase`
- 工具/辅助函数: `camelCase`

**变量:**
- 状态变量: `camelCase`
- 常量: `UPPER_SNAKE_CASE`

**类型:**
- 接口 (Interfaces): `PascalCase` (如 `interface AuxiliaryItem`)
- Props: `[ComponentName]Props`

## 代码风格

### 后端

**格式化:**
- 工具: `friendsofphp/php-cs-fixer`
- 配置: `.php-cs-fixer.dist.php`
- 标准: PSR-12（由 PHP-CS-Fixer 默认隐含）

**代码检查 (Linting):**
- 工具: `phpstan/phpstan` 和 `vimeo/psalm`
- 规则: `.phpstan.neon.dist`, `psalm.xml`

### 前端

**格式化:**
- 工具: `eslint`（通常集成了类似 Prettier 的规则）
- 配置: `frontend/eslint.config.js`

**代码检查 (Linting):**
- 工具: `eslint` 配合 `@typescript-eslint/parser`

## 导入组织 (Import Organization)

### 后端
1. PHP 标准库
2. 框架 (Laravel/Apiato)
3. 内部 App 命名空间 (`App\Containers\...`)
4. 内部 Ship 命名空间 (`App\Ship\...`)

### 前端
1. React 及 React 相关库
2. UI 库 (如 `antd`)
3. 内部组件
4. API 客户端
5. 样式/类型

## 错误处理

### 后端
- 在适当的情况下使用自定义异常。
- Repositories 将数据库约束冲突封装在 `Apiato\Core\Repositories\Exceptions\ResourceCreationFailed` 中。
- Actions 应处理高层逻辑，并可能抛出/重新抛出异常，由框架的异常处理器捕获。

### 前端
- 全局错误边界 `ErrorBoundary.tsx` 位于 `frontend/src/components/ErrorBoundary.tsx`。
- API 响应拦截器在 `frontend/src/api/client.ts` 中全局处理 401（未授权）错误。

## 日志

**框架:** `Laravel Logging` (后端), `console` (前端)

**模式:**
- 后端: 对于关键错误或审计日志使用 `Log` facade。
- 前端: 对于服务/组件层捕获的 API 错误使用 `console.error`。

## 模块设计

### 后端 (Porto 架构)
- **Actions:** 编排业务流程，调用多个 Tasks。
- **Tasks:** 单一职责的逻辑单元。
- **Models:** 位于 `Data/Models` 的 Eloquent 模型。
- **Repositories:** 位于 `Data/Repositories` 的数据访问层。

### 前端
- **Components:** 配合 Hooks 的函数式组件。
- **Pages:** 位于 `src/pages` 的顶层视图组件。
- **API Services:** 在 `src/api/` 中按领域组织。

---

*规范分析: 2025-11-20*
