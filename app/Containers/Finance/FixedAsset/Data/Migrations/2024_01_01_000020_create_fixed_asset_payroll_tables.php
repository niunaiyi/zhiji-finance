<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fixed Asset tables
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->comment('固定资产卡片表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('asset_no', 30)->comment('资产编号');
            $table->string('name', 100)->comment('资产名称');
            $table->string('category', 50)->comment('资产类别');
            $table->date('purchase_date')->comment('购入日期');
            $table->decimal('original_value', 15, 2)->comment('资产原值');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0)->comment('累计折旧');
            $table->decimal('net_value', 15, 2)->comment('资产净值');
            $table->enum('depreciation_method', ['straight_line', 'double_declining'])->comment('折旧方法:平均年限法/双倍余额递减法');
            $table->integer('useful_life_months')->comment('预计使用月份');
            $table->decimal('residual_rate', 5, 4)->comment('预计净残值率');
            $table->enum('status', ['active', 'disposed'])->default('active')->comment('状态:使用中/已处置');
            $table->timestamps();
        });

        Schema::create('depreciation_schedules', function (Blueprint $table) {
            $table->comment('折旧计划表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('fixed_asset_id')->comment('固定资产ID');
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->decimal('depreciation_amount', 15, 2)->comment('本期折旧额');
            $table->boolean('is_posted')->default(false)->comment('是否已过账');
            $table->timestamps();

            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
        });

        // Payroll tables
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->comment('工资项目表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('code', 20)->comment('项目代码');
            $table->string('name', 50)->comment('项目名称');
            $table->enum('type', ['earning', 'deduction'])->default('earning')->comment('类型:收入/扣款');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->comment('工资发放主表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->string('payroll_no', 30)->comment('发放表编号');
            $table->date('payroll_date')->comment('发放日期');
            $table->enum('status', ['draft', 'approved', 'posted'])->default('draft')->comment('状态:草稿/已审批/已记账');
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
        });

        Schema::create('payroll_lines', function (Blueprint $table) {
            $table->comment('工资发放明细表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('payroll_id')->comment('工资发放表ID');
            $table->unsignedBigInteger('employee_id')->comment('职员ID(对应aux_items.id)');
            $table->unsignedBigInteger('dept_id')->nullable()->comment('部门ID');
            $table->decimal('total_earning', 15, 2)->default(0)->comment('应发合计');
            $table->decimal('total_deduction', 15, 2)->default(0)->comment('扣款合计');
            $table->decimal('net_pay', 15, 2)->comment('实发工资');
            $table->timestamps();

            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('aux_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_lines');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('depreciation_schedules');
        Schema::dropIfExists('fixed_assets');
    }
};
