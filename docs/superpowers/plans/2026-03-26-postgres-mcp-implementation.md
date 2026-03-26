# Superpower 实施计划：Postgres MCP 集成

> **工作项编号**：SP-004
> **目标**：完成 Postgres MCP 服务在 Antigravity 环境下的安装与联调。

## 任务列表

### 1. 环境验证与依赖准备
- [x] 验证 Node.js (v22+) 环境。
- [x] 验证 npx (v11+) 环境。
- [x] 提取 `.env` 中的数据库连接参数。

### 2. 配置注入与联调
- [ ] 生成标准配置 JSON 块。
- [ ] 更新本地 Agent 配置文件 (Antigravity/Claude Desktop)。
- [ ] 运行测试查询：`SELECT count(*) FROM companies;`

### 3. 规范文档同步
- [ ] 在 `docs/superpowers` 中归档设计文档。
- [ ] 更新 `walkthrough.md` 记录此新增能力。

---

## 验证计划
- **工具发现测试**：重启后，确保 `postgres:list_tables` 出现在可用工具列表中。
- **数据一致性校验**：通过 MCP 工具查询出的 `company_id` 与代码逻辑绑定的 ID 是否一致。
