# 外部集成 (External Integrations)

**分析日期:** 2025-05-22

## API 与外部服务

**电子邮件:**
- Postmark - 事务性邮件投递。
  - SDK/客户端: Laravel 核心集成。
  - 认证: `POSTMARK_TOKEN`。
- Amazon SES - 基于 AWS 的邮件服务。
  - SDK/客户端: Laravel 核心（需 AWS SDK）。
  - 认证: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`。
- Resend - 现代邮件 API。
  - SDK/客户端: Laravel 集成。
  - 认证: `RESEND_KEY`。

**消息通知:**
- Slack 通知 - 通过 Webhook/Bot 集成。
  - SDK/客户端: Laravel Slack 通知频道。
  - 认证: `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL`。

## 数据存储

**数据库:**
- SQLite (开发/测试默认) - 基于文件的数据库。
- MySQL / MariaDB (生产/标准) - 可扩展的关系数据库。
- PostgreSQL / SQL Server (支持) - 企业级数据库。
  - 连接配置: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`。
  - 客户端: Eloquent ORM (Laravel 标准)。

**文件存储:**
- 本地文件系统 - 用于私有/公共资产的默认存储。
- Amazon S3 - 用于可扩展文件管理的云存储。
  - 服务: `S3` 驱动。
  - 连接配置: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_REGION`, `AWS_ENDPOINT`。

**缓存:**
- Redis - 用于缓存和队列的高性能键值存储。
  - 客户端: `phpredis`。
  - 连接配置: `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`, `REDIS_DB`。
- 本地缓存 (Local Cache) - 作为后备的文件缓存。

## 认证与身份

**认证提供者:**
- 自定义 OAuth2 服务器 - 使用 Laravel Passport 实现。
  - 实施方案: 基于 JWT 的 `Passport` API 守护程序。
  - 存储: 在数据库中存储 OAuth 客户端、访问令牌和刷新令牌。

**基于角色的访问控制 (RBAC):**
- Spatie Permissions - 管理用户的角色和权限。
  - 实施方案: 用户模型使用 `HasRoles` 和 `HasPermissions` Traits。

## 监控与可观测性

**错误追踪:**
- Laravel Debugbar - 面向开发者的调试工具。
  - 配置文件: `app/Ship/Configs/debugbar.php`。

**日志:**
- Laravel 日志 - 支持各种驱动（single, daily, slack, syslog）。
  - 实施方案: 代码中使用 `Log` facade。
  - 配置文件: `config/logging.php`。

## CI/CD 与部署

**托管:**
- 在支持 PHP 的服务器（如 DigitalOcean, AWS, Forge）上自托管。

**CI 流水线:**
- PHPUnit - CI 工作流中的单元测试和集成测试。
- Playwright - CI 中的前端 E2E 测试。

## 环境配置

**必需环境变量:**
- `APP_KEY` - 应用程序加密密钥。
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - 数据库凭据。
- `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY` - OAuth2 签名密钥。
- `APP_URL` - 应用程序根 URL。

**密钥存储位置:**
- 本地开发使用 `.env` 文件。
- 生产环境使用环境变量。

## Webhooks 与回调

**传入:**
- `passport` 端点: `/oauth/token`, `/oauth/authorize`（标准端点）。

**传出:**
- Slack 通知 - 发送提醒或更新到 Slack 频道。

---

*集成审计: 2025-05-22*
