# FixedAsset 模块规范

**所属 Agent：** Agent 4
**职责：** 固定资产台账、折旧计提、资产处置

## 折旧方法
- straight_line：直线法（原值 - 残值）/ 使用年限月数
- double_declining：双倍余额递减法

## 月末折旧（CalculateDepreciationAction）
1. 查询所有 status=active 的资产
2. 按折旧方法计算本月折旧额
3. 写入 `depreciation_schedules`
4. 更新 `fixed_assets.accumulated_depreciation` 和 `net_value`
5. 抛出 `DepreciationCalculatedEvent`（携带按部门汇总的折旧明细）
6. 抛出 `FixedAssetPeriodClosedEvent`

## 资产处置
处置时生成凭证：借:累计折旧+资产处置损益 / 贷:固定资产原值
