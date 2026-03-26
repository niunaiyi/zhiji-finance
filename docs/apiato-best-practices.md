# Apiato 常用命令与使用最佳实践

本文档总结了在使用 Apiato (Porto SAP 架构) 进行开发时的常用 Artisan 命令以及开发最佳实践。

## 1. 简介
Apiato 是一个基于 Laravel 构建的框架，实现了 Porto 软件架构模式（Porto SAP）。它强调代码的模块化、复用性以及清晰的关注点分离。

## 2. 常用生成命令 (Generator Commands)

在 Apiato 中，应当避免手动创建目录和基础文件，尽可能使用 `apiato:generate` 系列命令来生成基础设施代码，以确保所有文件符合 Porto 架构的规范和命名约定。

| 命令 | 描述 | 使用场景 |
|---|---|---|
| `php artisan apiato:generate:container` | 生成一个全新的 Container (包含基本结构) | 创建新的业务模块时 |
| `php artisan apiato:generate:action` | 生成 Action 类 | 处理一个具体的业务用例 (Use Case) |
| `php artisan apiato:generate:task` | 生成 Task 类 | 实现单一的、可复用的业务逻辑片段 |
| `php artisan apiato:generate:model` | 生成 Model | 定义数据库表的数据模型 |
| `php artisan apiato:generate:migration` | 生成 Migration | 修改或创建数据库表结构 |
| `php artisan apiato:generate:repository`| 生成 Repository | 处理数据库查询的数据访问层 |
| `php artisan apiato:generate:controller`| 生成 Controller (API或Web) | 处理进来的 HTTP 请求并协调 Action |
| `php artisan apiato:generate:request` | 生成 Request | 处理用户输入验证(rules)和鉴权(authorize) |
| `php artisan apiato:generate:route` | 生成 Route | 定义 API 端点路由 |
| `php artisan apiato:generate:transformer`| 生成 Transformer | 格式化模型以便响应 API |

**常用高级组合命令示例：**
```bash
# 生成一个完整的 API 端点 (自动生成 Route, Controller, Request, Action)
php artisan apiato:generate:endpoint

# 生成完整的 CRUD 结构 (包含 Model, Migration, Repository, Controller, Route 等全套依赖)
php artisan apiato:generate:crud
```

*(注意：不同版本的 Apiato 提供的具体生成器可能略有不同，在终端中运行 `php artisan list apiato` 可检查当前环境支持的全部生成命令)*

## 3. 架构组件最佳实践 (Porto 规范)

### 3.1 UI 层 (User Interface)
- **Controller (控制器):** 必须保持极薄。**严禁在 Controller 中编写业务逻辑**。Controller 的唯一职责是：
  1. 接收 Request (请求自动进行验证)。
  2. 提取并组装需要的数据传递给 Action。
  3. 接收 Action 的返回结果，并将其传给 Transformer 进行格式化 (基于 Fractal)。
- **Request (请求类):** 所有的用户输入验证规则 (Validation Rules) 和接口权限检查 (Authorization) 都必须在对应的 Request 类中完成。不要在代码中手动使用 `Validator::make`。

### 3.2 核心业务层 (Application Layer)
- **Action (动作):** 代表系统中的一个具体“用例 (Use Case)”或用户操作。Action 本身通常不包含复杂的业务判定逻辑，而是负责**调度(Orchestrate)不同的任务(Task)**来达成目标。可以把 Action 当作一个指挥官。
- **Task (任务):** 真正包含核心业务逻辑的地方。每个 Task **必须**只做一件独立、纯粹的事情 (严格贯彻单一职责原则)。Task 被高度设计为可复用组件，例如 `FindUserByIdTask` 或 `CreateInvoiceTask`，它们可以且应该被多个不同的 Action 互相调用。

### 3.3 数据层 (Data)
- **Model (模型):** 代表数据库结构，在此处定义 Eloquent 的关联 (Relationships)、隐藏字段 (`$hidden`)、可批量赋值字段 (`$fillable`) 和属性防丢转换 (`$casts`)。
- **Repository (仓库):** 将所有对数据库的直接查询封装进 Repository 中（配合 L5-Repository 包使用）。尽量避免在 Task 中直接写入 `User::where('status', 1)->get()`，应当在 Repository 中编写一个方法如 `findActiveUsers()`，或者直接使用预制结构 `$userRepository->findWhere(['status' => 1])`。

## 4. 日常开发工作流推荐

开发一个新的业务需求（或一个新接口）推荐按照以下自底向上的顺序进行：

1. **结构规划**：分析本次需求属于哪个现有的 Container，或者是否需要创建一个全新的 Container (`php artisan apiato:generate:container`)。
2. **数据基础建设 (Data)**：
   - 运行 `php artisan apiato:generate:model` 和 `php artisan apiato:generate:migration` 建表。
   - 补全 Model 字段和执行 `php artisan migrate`。
   - 生成对应的 Repository。
3. **实现核心逻辑 (Tasks)**：
   - 剥离出业务所需的基础步骤，为每个步骤生成对应的 Task (`php artisan apiato:generate:task`)。
   - 实现 Task 中的确切逻辑以及异常抛出。
4. **组装核心逻辑 (Action)**：
   - 生成 Action (`php artisan apiato:generate:action`)。
   - 在 Action 内通过依赖注入或 `app(Task::class)->run()` 调用刚才写好的各项 Task。
5. **暴露访问接口 (UI)**：
   - 建立外部访问的通道，定义 Route (`php artisan apiato:generate:route`) 绑定权限拦截。
   - 编写 Request (`php artisan apiato:generate:request`) 确保传给 Action 的数据是干净、安全的。
   - 编写 Controller (`php artisan apiato:generate:controller`) 中转数据。
   - 编写 Transformer (`php artisan apiato:generate:transformer`) 过滤或嵌套返回给前端的数据格式。

## 5. 常规问题解决与排错 (Troubleshooting)

- **“找不到类 (Class not found)” 或 “路由未生效 (Route not defined)”:** 
  新生成了文件但框架还没有反应过来，及时清理缓存：
  ```bash
  composer dump-autoload
  php artisan optimize:clear
  ```
- **跨 Container 调用规则 (Cross-Container Calling):**
  - **允许:** 一个 Container 的 Action 调用另个 Container 的 Task (鼓励复用)。
  - **禁止:** 一个 Task 调用另个 Task (Task应该是原子的底层的)。
  - **禁止:** UI 层跨 Controller 或直接调用其他 Container 的 Controller。
- **依赖解析异常:** 
  如果遇到实例化 Action/Task 报错，检查其 `__construct` 内注入的类命名空间 (Namespace) 是否由于手滑输错。由于 Apiato 高度依赖服务容器 (Service Container) 的自动注入，类型提示 (Type Hint) 错误会导致致命错误。

## 6. 其他常用基础命令
因为 Apiato 建立在 Laravel 之上，Laravel 原生命令都是通用的，推荐熟练使用：
- `php artisan route:list` - 查看所有注册的路由及其附加的中介/权限。
- `php artisan migrate` - 运行数据库迁移。
- `php artisan migrate:rollback` - 撤销最后一次执行的迁移操作。
- `php artisan db:seed` - 使用 Seeders 填充测试或初始数据。
- `php artisan tinker` - 进入交互式 PHP 命令行环境，便于快速调用 Model 或测试逻辑。
