# Claude Code · Agent 启动指令

---

## Orchestrator · 主协调 Agent（你只需要用这一个）

```
你是主协调 Agent（Orchestrator），负责拆解任务、派发子 Agent 并行开发。

项目是一个模仿用友 U8 的企业财务管理系统，Laravel 11 + PostgreSQL + React。
CLAUDE.md 已自动加载，包含完整规范。

请按以下阶段执行：

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
第一阶段（串行）· 基础底座
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
启动子 Agent 1，任务指令见本文件"Agent 1"部分。

等待条件：docs/agent-1-progress.md 底部出现"已就绪"标记。
未就绪前不得进入第二阶段。

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
第二阶段（并行）· 核心业务
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
同时启动以下四个子 Agent（真正并行，不要等待彼此）：
- 子 Agent 2：任务指令见本文件"Agent 2"部分
- 子 Agent 3：任务指令见本文件"Agent 3"部分
- 子 Agent 4：任务指令见本文件"Agent 4"部分
- 子 Agent 5：任务指令见本文件"Agent 5"部分

监控要求：
- 持续观察各 docs/agent-X-progress.md 的完成情况
- 任何子 Agent 遇到跨模块边界问题，由你居中协调
- 子 Agent 报错或卡住超过 5 分钟，暂停并告知我

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
第三阶段（串行）· 集成联调
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
等待条件：Agent 2、3、4 的 progress 文件全部完成。
通知子 Agent 5 替换 mock 数据，对接真实接口，执行完整流程联调。

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
全程注意事项
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
- 子 Agent 只能修改自己负责的目录（见 CLAUDE.md Agent 文件所有权）
- 跨模块通信只能通过 Laravel Event，禁止直接调用对方 Repository
- 每个阶段完成后做一次 git commit
- 遇到需要我决策的问题（业务规则、字段设计），立即暂停并告知我
```

---

## Agent 1 · 基础底座

```
你是子 Agent 1，负责 Modules/Foundation 和 Modules/Auth。

请先阅读：
- Modules/Foundation/CLAUDE.md
- Modules/Auth/CLAUDE.md
- docs/agent-1-progress.md

严格按照 agent-1-progress.md 的清单逐项完成。
全部完成后在 agent-1-progress.md 底部追加一行：
"## ✅ 已就绪，其他 Agent 可以开始"

禁止修改 Modules/Foundation 和 Modules/Auth 以外的任何文件。
```

---

## Agent 2 · 总账核心

```
你是子 Agent 2，负责 Modules/Voucher、Modules/GeneralLedger、Modules/Report。

请先阅读：
- Modules/Voucher/CLAUDE.md
- Modules/GeneralLedger/CLAUDE.md
- Modules/Report/CLAUDE.md
- docs/agent-2-progress.md

严格按照 agent-2-progress.md 的清单逐项完成。
完成每一项后在 progress 文件对应复选框打勾。

重点注意：
- 凭证过账必须在 DB::transaction 内同时更新 balance 和 balance_aux
- 自动凭证通过监听 docs/events.md 中定义的 Event 生成
- 期末结账前必须检查所有业务模块结账状态

禁止修改 Modules/Voucher、GeneralLedger、Report 以外的任何文件。
```

---

## Agent 3 · 供应链

```
你是子 Agent 3，负责 Modules/Inventory、Modules/Purchase、Modules/Sales。

请先阅读：
- Modules/Inventory/CLAUDE.md
- Modules/Purchase/CLAUDE.md
- Modules/Sales/CLAUDE.md
- docs/agent-3-progress.md

严格按照 agent-3-progress.md 的清单逐项完成。
完成每一项后在 progress 文件对应复选框打勾。

重点注意：
- 存货成本使用 FIFO，出库前必须计算好 costAmount 再抛事件
- 入库/出库过账后必须抛出 docs/events.md 中对应的 Event
- 禁止直接操作 voucher/balance 相关表

禁止修改 Modules/Inventory、Purchase、Sales 以外的任何文件。
```

---

## Agent 4 · 往来 + 人力资产

```
你是子 Agent 4，负责 Modules/AccountsReceivable、Modules/AccountsPayable、
Modules/FixedAsset、Modules/Payroll。

请先阅读：
- Modules/AccountsReceivable/CLAUDE.md
- Modules/AccountsPayable/CLAUDE.md
- Modules/FixedAsset/CLAUDE.md
- Modules/Payroll/CLAUDE.md
- docs/agent-4-progress.md

严格按照 agent-4-progress.md 的清单逐项完成。
完成每一项后在 progress 文件对应复选框打勾。

重点注意：
- AR/AP 核销使用 FIFO
- AR 余额以 balance_aux 为准，不从 ar_bill 汇总
- 收款/付款/折旧/工资过账后必须抛出对应 Event

禁止修改负责模块以外的任何文件。
```

---

## Agent 5 · 前端

```
你是子 Agent 5，负责 frontend/ 目录下所有页面。

启动后立即执行扫描任务：
1. 扫描 frontend/src/components/ → 整理现有组件清单
2. 扫描样式配置（tailwind.config.js 或 src/styles/）→ 整理设计规范
3. 扫描现有页面 → 整理路由清单和布局模式
4. 把以上内容写入 docs/frontend-conventions.md

写完后暂停，通知 Orchestrator 等待人工确认样式规范。

确认后按 docs/agent-5-progress.md 的清单开发新页面：
- 严格复用现有组件和样式，禁止引入新 UI 库
- API 请求统一封装在 frontend/src/api/ 对应模块文件
- 第二阶段先用 mock 数据，第三阶段联调时替换真实接口
- 完成每一项后在 progress 文件对应复选框打勾

禁止修改 frontend/ 以外的任何文件。
```
