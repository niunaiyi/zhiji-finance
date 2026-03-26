# 设计文档：简化认证体系 (JWT 替换 Passport)

*   **日期**：2026-03-26
*   **状态**：已批准
*   **主题**：将 Apiato 项目的 OAuth2/Passport 认证体系简化为纯 JWT 认证。

## 1. 背景与动机
当前系统使用 Laravel Passport (OAuth2)，对于简单的财务系统来说过于复杂。前端需要管理 Client ID/Secret，且服务端需要维护多张 OAuth 相关表。为了简化登录流程并提升响应性能，决定迁移至 `tymon/jwt-auth`。

## 2. 架构设计

### 2.1 认证流程
1.  **登录**：用户提交 `email` 和 `password` 到 `/v1/login`。
2.  **颁发**：服务端验证成功后，生成 JWT 并返回给前端。
3.  **鉴权**：前端在请求头中携带 `Authorization: Bearer {token}`。
4.  **校验**：`auth:api` 中间件（驱动切换为 `jwt`）校验 Token 合法性。
5.  **刷新**：Token 到期后，前端请求 `/v1/refresh` 获取新 Token，旧 Token 自动进入黑名单。

### 2.2 核心组件修改
*   **User 模型** (`App\Containers\AppSection\User\Models\User`)：
    *   实现 `Tymon\JWTAuth\Contracts\JWTSubject` 接口。
    *   实现 `getJWTIdentifier()` 和 `getJWTCustomClaims()` 方法。
*   **配置** (`config/auth.php`)：
    *   `guards.api.driver` 改为 `jwt`。
*   **Actions**：
    *   新建 `ProxyApiLoginAction`：封装登录逻辑。
    *   新建 `ProxyApiRefreshAction`：封装刷新逻辑。
    *   新建 `ProxyApiLogoutAction`：封装注销逻辑。

## 3. 技术选型
*   **库**：`tymon/jwt-auth:^2.1`
*   **存储**：JWT 为无状态，黑名单存储使用 Laravel Cache 驱动（Redis 或 Database）。

## 4. 实施步骤
1.  **清理旧环境**：
    *   `composer remove laravel/passport`。
    *   删除 Passport 配置文件及迁移。
    *   移除 `AppSection` 中引用 Passport 的路由。
2.  **安装与配置**：
    *   `composer require tymon/jwt-auth`。
    *   发布配置并生成密钥 `php artisan jwt:secret`。
    *   修改 `User` 模型。
3.  **实现功能**：
    *   重写登录路由、控制器和 Action。
    *   实现刷新和注销逻辑。
4.  **清理数据库**：
    *   删除 `oauth_access_tokens`, `oauth_refresh_tokens` 等表。

## 5. 安全性考量
*   **短效 Token**：TTL 设为 60 分钟。
*   **黑名单机制**：启用黑名单防止被注销或刷新的 Token 被重复利用。
*   **刷新宽限期**：设为 30 秒，解决前端并发请求时，首个请求刷新 Token 导致其他请求携带旧 Token 失效的问题。

## 6. 测试计划
*   **功能测试**：验证登录、刷新、注销流程。
*   **中间件测试**：验证携带有效/无效/过期 Token 访问受限接口的结果。
*   **并发测试**：验证刷新宽限期是否生效。
