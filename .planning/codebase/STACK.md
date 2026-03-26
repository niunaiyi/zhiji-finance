# 技术栈 (Technology Stack)

**分析日期:** 2025-05-22

## 编程语言

**主要:**
- PHP 8.2 - 后端核心逻辑、API 开发和框架基础设施。
- TypeScript 5.9 - 前端应用逻辑、类型安全和组件定义。

**次要:**
- JavaScript - 构建脚本和遗留/工具配置。
- CSS/PostCSS - 通过 Tailwind CSS 框架进行样式设计。
- Blade - 有限使用于服务器端渲染的视图（主要是以 API 为中心的应用）。

## 运行时环境 (Runtime)

**环境:**
- PHP 8.2+
- Node.js (Vite 7 建议使用最近的 LTS)。

**包管理器:**
- Composer 2.x - 后端依赖管理。
  - 锁定文件: `composer.lock`。
- npm - 前端依赖管理。
  - 锁定文件: `frontend/package-lock.json`。

## 框架 (Frameworks)

**核心:**
- Laravel 11 - 基础 PHP 框架。
- Apiato 13 - 基于 Laravel 的架构框架，实现了 "Porto" 模式。
- React 19.2 - 用于构建用户界面的前端库。

**测试:**
- PHPUnit 11 - 主要后端测试框架。
- Playwright 1.58 - 前端端到端 (E2E) 测试。
- Mockery 1.4 - 用于 PHP 测试的 Mock 框架。

**构建/开发:**
- Vite 7.3 - 前端构建工具和开发服务器。
- Laravel Vite Plugin 1.2 - Laravel 与 Vite 之间的集成。
- Tailwind CSS 4.1 - 原子化 CSS 框架。

## 关键依赖

**关键项:**
- `apiato/core` ^13.1 - 提供 Porto 架构和核心 API 功能。
- `laravel/passport` ^13.0 - OAuth2 认证与 API Guard 实现。
- `spatie/laravel-permission` ^6.0 - 基于角色的访问控制 (RBAC)。
- `antd` ^6.3.0 - React 的 Ant Design UI 组件库。
- `zustand` ^5.0.11 - 轻量级前端状态管理。
- `axios` ^1.13.5 - 基于 Promise 的 HTTP 客户端。

**基础设施:**
- `wikimedia/composer-merge-plugin` ^2.1 - 合并容器特定的 `composer.json` 文件。
- `fractal` (通过 Apiato) - API 响应的数据转换层。
- `hashids` - 用于 API 接口的 ID 混淆。

## 配置管理

**环境:**
- 通过 `.env` 文件管理（参见 `.env.example` 获取必需的 key）。
- 关键配置: `APP_KEY`, `DB_CONNECTION`, `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY`。

**构建:**
- `vite.config.ts` - 前端构建配置。
- `composer.json` - 后端依赖和脚本配置。
- `tailwind.config.js` - 样式配置。

## 平台要求

**开发环境:**
- PHP 8.2 或更高版本。
- MySQL/SQLite/PostgreSQL/MariaDB。
- Node.js & npm。
- Composer。

**生产环境:**
- Linux (建议)。
- Web 服务器 (Nginx/Apache) 配备 PHP-FPM。
- 数据库 (建议使用 MySQL/PostgreSQL)。
- Redis (可选，用于缓存和队列)。

---

*技术栈分析: 2025-05-22*
