# Superpower 设计文档：Postgres MCP 数据库集成

*   **日期**：2026-03-26
*   **状态**：初稿
*   **主题**：为 Antigravity 及其它 AI Agent 提供直接访问 PostgreSQL 数据库的能力。

## 1. 背景与目标
为了提升 Agent 在开发过程中对实体数据、表结构及业务状态的感知能力，集成 Model Context Protocol (MCP) 的 Postgres 服务。这使得 Agent 无需通过手动执行 `php artisan` 或 `psql` 命令即可：
- 快速检索表结构及字段说明。
- 验证数据迁移是否成功。
- 在调试过程中检查特定记录的状态（如凭证金额、核销状态等）。

## 2. 功能设计

### 2.1 核心工具集 (Tools)
集成官方 `@modelcontextprotocol/server-postgres` 提供的工具：
- `list_tables`: 列出财务系统中所有的业务表。
- `describe_table`: 获取指定表的 DDL 及字段元数据（如 `accounts`, `vouchers`, `companies`）。
- `query`: 执行只读 SQL 查询，支持数据分析及一致性检查。

### 2.2 连接策略
- **本地开发**：通过 `npx` 直接连接 `.env` 中定义的 `DB_HOST`。
- **Agent 持久化支持**：将配置注入 `claude_desktop_config.json` 或项目的 `.antigravity/mcp_config.json`（如果支持）。

## 3. 安全性 & 隔离
- **只读权限**：默认强制使用只读连接字符串，防止 Agent 意外修改核心账务数据。
- **租户隔离感知**：Agent 在执行查询时应遵循 `company_id` 过滤逻辑（虽然 MCP 层面是原始 SQL，但 Agent 需在 Prompt 层面被引导）。

## 4. 实施计划 (Roadmap)
1. **依赖确认**：验证系统 Node.js 及 npx 环境。
2. **连接初始化**：根据 `.env` 自动生成 MCP 配置。
3. **能力测试**：执行示例查询（如统计 `accounts` 数量）验证链路通畅。

## 5. 预期效果
Agent 能够具备“数据库透视”能力，极大缩减定位数据 BUG 的时间。
