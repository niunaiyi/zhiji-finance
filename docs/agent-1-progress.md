# Agent 1 · 基础底座 · 进度

## 负责模块
- Modules/Foundation
- Modules/Auth
- database/migrations/global/
- database/migrations/tenant/

## 状态

### database/migrations/
- [ ] global/create_companies_table
- [ ] global/create_users_table
- [ ] global/create_user_company_roles_table
- [ ] tenant/create_accounts_table
- [ ] tenant/create_aux_categories_table
- [ ] tenant/create_aux_items_table
- [ ] tenant/create_account_aux_categories_table
- [ ] tenant/create_periods_table

### Modules/Auth
- [ ] 用户注册/登录
- [ ] 获取所属账套列表
- [ ] 选择账套 → 签发带 company_id 的 JWT
- [ ] SwitchTenant 中间件
- [ ] BelongsToCompany GlobalScope Trait

### Modules/Foundation
- [ ] 科目体系 CRUD（含层级）
- [ ] 辅助核算类别 CRUD
- [ ] 辅助核算项目 CRUD
- [ ] 科目挂载辅助核算
- [ ] 会计期间初始化（开账）
- [ ] 期间状态管理（open/closed/locked）
- [ ] 内置科目表种子数据

---

## 其他 Agent 开始的前置条件
> Migration 全部完成 + BelongsToCompany GlobalScope 可用后，在此标记：
> [ ] **已就绪，其他 Agent 可以开始**
