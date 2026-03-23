# Auth 模块规范

**所属 Agent：** Agent 1
**职责：** 用户认证、账套管理、角色权限

## 登录流程
1. `POST /api/auth/login` → 验证账号密码 → 返回用户信息 + 所属账套列表
2. `POST /api/auth/select-company` → 选择账套 → 签发带 company_id 的 JWT
3. 后续所有请求经过 `SwitchTenant` 中间件自动注入 company_id

## JWT Payload 结构
```json
{
  "sub": 1,
  "company_id": 3,
  "role": "accountant"
}
```

## SwitchTenant 中间件
- 从 JWT 解析 company_id
- 验证用户有权访问该账套
- `app()->instance('current.company_id', $companyId)`
- 注册到所有 `/api/tenant/*` 路由

## 角色权限
- admin：所有操作
- accountant：凭证填制/审核，业务单据
- auditor：只读 + 审核
- viewer：只读

## 路由分组
```
/api/console/*   → 不需要账套（登录/注册/选账套）
/api/tenant/*    → 需要账套（所有业务接口）
```
