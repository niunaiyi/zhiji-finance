# 数据库表结构（Schema）

> 所有业务表含 `company_id`（GlobalScope 自动注入）
> 金额字段统一 `decimal(15,2)`

---

## 全局表（不含 company_id）

### companies · 账套/公司
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| code | varchar(20) | 账套代码，唯一 |
| name | varchar(100) | 公司名称 |
| fiscal_year_start | tinyint | 会计年度开始月份，默认1 |
| status | enum | active/suspended |
| created_at | timestamp | |

### users · 全局用户
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| name | varchar(50) | |
| email | varchar(100) | 唯一 |
| password | varchar | |
| created_at | timestamp | |

### user_company_roles · 用户-账套-角色
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| user_id | bigint FK | |
| company_id | bigint FK | |
| role | enum | admin/accountant/auditor/viewer |
| is_active | boolean | |

---

## Foundation 模块（Agent 1）

### accounts · 会计科目
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| code | varchar(20) | 科目编码，4位一级 |
| name | varchar(100) | 科目名称 |
| parent_id | bigint | 上级科目 |
| level | tinyint | 级次 |
| element_type | enum | asset/liability/equity/income/expense/cost |
| balance_direction | enum | debit/credit |
| is_detail | boolean | 是否末级科目 |
| is_active | boolean | |
| has_aux | boolean | 是否启用辅助核算 |
| INDEX | (company_id, code) | |

### aux_categories · 辅助核算类别
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| code | varchar(20) | customer/supplier/dept/employee/inventory/project |
| name | varchar(50) | |
| is_system | boolean | 系统内置不可删 |

### aux_items · 辅助核算项目
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| aux_category_id | bigint FK | |
| code | varchar(50) | |
| name | varchar(100) | |
| parent_id | bigint | 支持层级 |
| is_active | boolean | |
| extra | jsonb | 扩展字段 |
| INDEX | (company_id, aux_category_id, code) | |

### account_aux_categories · 科目挂载辅助核算
| 字段 | 类型 | 说明 |
|------|------|------|
| account_id | bigint FK | |
| aux_category_id | bigint FK | |
| is_required | boolean | |
| sort_order | tinyint | |
| PRIMARY KEY | (account_id, aux_category_id) | |

### periods · 会计期间
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| fiscal_year | smallint | |
| period_number | tinyint | 1-12 |
| start_date | date | |
| end_date | date | |
| status | enum | open/closed/locked |
| closed_at | timestamp | |
| INDEX | (company_id, fiscal_year, period_number) | |

---

## Voucher 模块（Agent 2）

### vouchers · 凭证主表
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| period_id | bigint FK | |
| voucher_type | enum | receipt/payment/transfer |
| voucher_no | varchar(20) | 格式：2024-记-0001 |
| voucher_date | date | |
| status | enum | draft/reviewed/posted/reversed/voided |
| summary | varchar(200) | |
| total_debit | decimal(15,2) | |
| total_credit | decimal(15,2) | |
| source_type | varchar(50) | manual/purchase/sales/payroll/depreciation |
| source_id | bigint | 来源单据ID |
| created_by | bigint FK | |
| reviewed_by | bigint FK | |
| posted_by | bigint FK | |
| posted_at | timestamp | |
| INDEX | (company_id, period_id, status) | |
| INDEX | (company_id, voucher_date) | |

### voucher_lines · 凭证行
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| voucher_id | bigint FK | |
| line_no | tinyint | |
| account_id | bigint FK | |
| summary | varchar(200) | |
| debit | decimal(15,2) | |
| credit | decimal(15,2) | |

### voucher_line_aux · 凭证行辅助核算
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| voucher_line_id | bigint FK | |
| aux_category_id | bigint FK | |
| aux_item_id | bigint FK | |

### balances · 科目余额
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| period_id | bigint FK | |
| account_id | bigint FK | |
| opening_debit | decimal(15,2) | 期初借方 |
| opening_credit | decimal(15,2) | 期初贷方 |
| period_debit | decimal(15,2) | 本期借方发生额 |
| period_credit | decimal(15,2) | 本期贷方发生额 |
| closing_debit | decimal(15,2) | 期末借方 |
| closing_credit | decimal(15,2) | 期末贷方 |
| UNIQUE | (company_id, period_id, account_id) | |

### balance_aux · 辅助核算余额（AR余额权威来源）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| period_id | bigint FK | |
| account_id | bigint FK | |
| aux_category_id | bigint FK | |
| aux_item_id | bigint FK | |
| opening_debit | decimal(15,2) | |
| opening_credit | decimal(15,2) | |
| period_debit | decimal(15,2) | |
| period_credit | decimal(15,2) | |
| closing_debit | decimal(15,2) | |
| closing_credit | decimal(15,2) | |
| UNIQUE | (company_id, period_id, account_id, aux_category_id, aux_item_id) | |

---

## AccountsReceivable 模块（Agent 4）

### ar_bills · 应收单据
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| period_id | bigint FK | |
| bill_no | varchar(30) | |
| bill_date | date | |
| customer_id | bigint FK | aux_items.id |
| amount | decimal(15,2) | |
| settled_amount | decimal(15,2) | |
| balance | decimal(15,2) | |
| status | enum | open/partial/settled/voided |
| source_type | varchar(50) | sales_invoice/manual |
| source_id | bigint | |
| INDEX | (company_id, customer_id, status) | |

### ar_receipts · 收款单
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| period_id | bigint FK | |
| receipt_no | varchar(30) | |
| receipt_date | date | |
| customer_id | bigint FK | |
| amount | decimal(15,2) | |
| settled_amount | decimal(15,2) | |
| balance | decimal(15,2) | |
| status | enum | open/partial/settled |

### ar_settlements · 核销记录
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| ar_bill_id | bigint FK | |
| ar_receipt_id | bigint FK | |
| amount | decimal(15,2) | |
| settled_at | timestamp | |
| settled_by | bigint FK | |

---

## AccountsPayable 模块（Agent 4）

### ap_bills · 应付单据
（结构同 ar_bills，supplier_id 替代 customer_id，含 is_estimate 字段）

### ap_payments · 付款单
（结构同 ar_receipts）

### ap_settlements · 核销记录
（结构同 ar_settlements）

---

## Inventory 模块（Agent 3）

### inventories · 库存台账
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| inventory_id | bigint FK | aux_items(inventory类型) |
| warehouse_id | bigint FK | |
| qty | decimal(15,4) | |
| unit_cost | decimal(15,4) | FIFO最新层 |
| total_cost | decimal(15,2) | |

### inventory_transactions · 出入库流水
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| trans_type | enum | purchase_in/sales_out/transfer/adjust |
| inventory_id | bigint FK | |
| warehouse_id | bigint FK | |
| qty | decimal(15,4) | 正数入库，负数出库 |
| unit_cost | decimal(15,4) | |
| total_cost | decimal(15,2) | |
| source_type | varchar(50) | |
| source_id | bigint | |
| trans_date | date | |

---

## Purchase 模块（Agent 3）

### purchase_orders · 采购订单
### purchase_receipts · 采购入库单
### purchase_invoices · 采购发票

---

## Sales 模块（Agent 3）

### sales_orders · 销售订单
### sales_shipments · 销售出库单
### sales_invoices · 销售发票

---

## FixedAsset 模块（Agent 4）

### fixed_assets · 固定资产台账
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| asset_no | varchar(30) | |
| name | varchar(100) | |
| category | varchar(50) | |
| purchase_date | date | |
| original_value | decimal(15,2) | 原值 |
| accumulated_depreciation | decimal(15,2) | 累计折旧 |
| net_value | decimal(15,2) | 净值 |
| depreciation_method | enum | straight_line/double_declining |
| useful_life_months | int | |
| residual_rate | decimal(5,4) | 残值率 |
| status | enum | active/disposed |

### depreciation_schedules · 折旧计划
| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| company_id | bigint | |
| fixed_asset_id | bigint FK | |
| period_id | bigint FK | |
| depreciation_amount | decimal(15,2) | |
| is_posted | boolean | |

---

## Payroll 模块（Agent 4）

### payroll_items · 工资项目
### payrolls · 工资单（按期间）
### payroll_lines · 工资明细（每人每期）
