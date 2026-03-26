# 简化认证体系 (JWT) 实施计划

> **对于代理工作者**：要求子技能：使用 superpowers:subagent-driven-development（推荐）或 superpowers:executing-plans 逐个任务执行此计划。步骤使用复选框 (`- [ ]`) 语法进行跟踪。

**目标**：移除 OAuth2/Passport，改为使用 `tymon/jwt-auth` 实现无状态的 JWT 认证。

**架构**：将 `api` guard 驱动切换为 `jwt`。`User` 模型实现 `JWTSubject` 接口。新增 API 接口用于登录、注销和刷新 Token，支持黑名单机制和刷新宽限期。

**技术栈**：Laravel, Apiato, `tymon/jwt-auth`.

---

### 任务 1：清理旧环境 (Passport)

**文件**：
- 修改：`composer.json`
- 修改：`app/Ship/Parents/Models/UserModel.php`
- 修改：`app/Containers/AppSection/Authentication/UI/API/Routes/Passport.v1.private.php`

- [ ] **步骤 1：从 composer.json 中移除 Passport**
    - 运行：`composer remove laravel/passport`
- [ ] **步骤 2：从 UserModel 中移除 HasApiTokens 引用**
    - 文件：`app/Ship/Parents/Models/UserModel.php`
    - 操作：删除 `use Laravel\Passport\HasApiTokens;` 和 `use HasApiTokens;`
- [ ] **步骤 3：注销 Passport 路由**
    - 文件：`app/Containers/AppSection/Authentication/UI/API/Routes/Passport.v1.private.php`
    - 操作：清空该文件内容（保留 PHP 标签），以免产生路由冲突。
- [ ] **步骤 4：提交**
    - 运行：`git commit -am "chore: remove passport dependency and references"`

### 任务 2：安装与配置 JWT

**文件**：
- 修改：`config/auth.php`
- 修改：`.env`

- [ ] **步骤 1：安装 tymon/jwt-auth**
    - 运行：`composer require tymon/jwt-auth`
- [ ] **步骤 2：发布配置文件**
    - 运行：`php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"`
- [ ] **步骤 3：生成 JWT 密钥**
    - 运行：`php artisan jwt:secret`
- [ ] **步骤 4：修改 auth.php 驱动**
    - 文件：`config/auth.php`
    - 操作：将 `guards.api.driver` 从 `passport` 修改为 `jwt`。
- [ ] **步骤 5：在 .env 中设置安全参数**
    - 添加内容：
      ```env
      JWT_TTL=60
      JWT_REFRESH_TTL=20160
      JWT_BLACKLIST_ENABLED=true
      JWT_BLACKLIST_GRACE_PERIOD=30
      ```
- [ ] **步骤 6：提交**
    - 运行：`git add . && git commit -m "feat: install and configure tymon/jwt-auth"`

### 任务 3：更新 User 模型

**文件**：
- 修改：`app/Containers/AppSection/User/Models/User.php`

- [ ] **步骤 1：实现 JWTSubject 接口**
    - 修改 `User` 类定义，添加接口实现及必要方法：
      ```php
      use Tymon\JWTAuth\Contracts\JWTSubject;
      // ...
      final class User extends ParentUserModel implements JWTSubject {
          // ...
          public function getJWTIdentifier() { return $this->getKey(); }
          public function getJWTCustomClaims() { return []; }
      }
      ```
- [ ] **步骤 2：提交**
    - 运行：`git commit -am "feat: update User model to implement JWTSubject"`

### 任务 4：实现 API 认证逻辑

**文件**：
- 新建：`app/Containers/AppSection/Authentication/Actions/Api/ProxyApiLoginAction.php`
- 新建：`app/Containers/AppSection/Authentication/UI/API/Controllers/AuthController.php`
- 新建：`app/Containers/AppSection/Authentication/UI/API/Routes/Login.v1.private.php`

- [ ] **步骤 1：实现 ProxyApiLoginAction**
    - 编写登录逻辑，使用 `auth('api')->attempt($credentials)` 获取 Token 并返回。
- [ ] **步骤 2：创建 AuthController**
    - 实现 `login`, `logout`, `refresh` 方法，调用相应的 Action 或直接使用 `auth('api')`。
- [ ] **步骤 3：注册路由**
    - 在 `app/Containers/AppSection/Authentication/UI/API/Routes/` 下创建新的路由文件。
- [ ] **步骤 4：提交**
    - 运行：`git add . && git commit -m "feat: implement JWT login, logout and refresh endpoints"`

### 任务 5：清理与验证

- [ ] **步骤 1：清理 Passport 数据库表**
    - 编写一个临时的迁移或使用 `artisan` 命令删除 `oauth_*` 开头的表。
- [ ] **步骤 2：验证 JWT 鉴权**
    - 使用 Postman 或 curl 模拟登录，并携带 Token 访问 `/v1/me` 等受限接口，确保返回 200。
- [ ] **步骤 3：验证刷新机制**
    - 调用 `/v1/refresh` 接口，验证旧 Token 是否失效（进入黑名单），新 Token 是否有效。
- [ ] **步骤 4：提交**
    - 运行：`git commit -am "chore: cleanup passport tables and verify jwt auth"`
