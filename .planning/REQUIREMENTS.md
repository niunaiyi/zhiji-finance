# 需求列表 (REQUIREMENTS)

## 基础设置 (Foundation)
- [x] REQ-101: 多账套模型与 company_id 物理隔离
- [x] REQ-102: 会计科目体系 (4级层级)
- [x] REQ-103: 辅助核算类别与项目 (职员、客户、供应商、存货等)
- [x] REQ-104: 会计期间管理 (开账、结账)

## 总账核心 (Voucher & GL)
- [x] REQ-201: 凭证录入与分录管理
- [x] REQ-202: 借贷平衡校验规则
- [x] REQ-203: 实时余额计算 (Balances/BalanceAux)
- [x] REQ-204: 总账、明细账、科目余额表查询

## 供应链与往来 (Supply Chain & AR/AP)
- [x] REQ-301: 采购/销售流程基本单据
- [x] REQ-302: 存货成本核算 (移动加权平均)
- [x] REQ-303: 应收应付管理与 FIFO 核销逻辑

## 专项会计模块 (Specialized Modules)
- [x] REQ-401: 固定资产台账与自动折旧
- [x] REQ-402: 职员工资核算与发放流水

## 系统进阶需求 (Advanced System Features)
- [ ] REQ-501: 业务模块一键生成财务凭证 (Voucher Integration)
- [ ] REQ-502: 基于角色的访问控制 (RBAC)
- [ ] REQ-503: 跨账套数据隔离深度验证
- [ ] REQ-504: 期末自动结转损益
