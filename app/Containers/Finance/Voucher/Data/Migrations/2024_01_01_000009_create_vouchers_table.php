<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->comment('财务凭证主表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->enum('voucher_type', ['receipt', 'payment', 'transfer'])->comment('凭证类型:收款/付款/转账');
            $table->string('voucher_no', 20)->comment('凭证编号(例:2024-记-0001)');
            $table->date('voucher_date')->comment('凭证日期');
            $table->enum('status', ['draft', 'reviewed', 'posted', 'reversed', 'voided'])->default('draft')->comment('状态:草稿/已审核/已记账/已冲销/已作废');
            $table->string('summary', 200)->nullable()->comment('摘要');
            $table->decimal('total_debit', 15, 2)->default(0)->comment('合计借方');
            $table->decimal('total_credit', 15, 2)->default(0)->comment('合计贷方');
            $table->string('source_type', 50)->nullable()->comment('来源类型:manual/purchase/sales/payroll/depreciation');
            $table->unsignedBigInteger('source_id')->nullable()->comment('来源单据ID');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('审核人');
            $table->unsignedBigInteger('posted_by')->nullable()->comment('记账人');
            $table->timestamp('posted_at')->nullable()->comment('记账时间');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
            $table->index(['company_id', 'period_id', 'status']);
            $table->index(['company_id', 'voucher_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
