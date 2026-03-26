<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_bills', function (Blueprint $table) {
            $table->comment('应收账单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->string('bill_no', 30)->comment('账单编号');
            $table->date('bill_date')->comment('账单日期');
            $table->unsignedBigInteger('customer_id')->comment('客户ID(对应aux_items.id)');
            $table->decimal('amount', 15, 2)->comment('原始金额');
            $table->decimal('settled_amount', 15, 2)->default(0)->comment('已结算金额');
            $table->decimal('balance', 15, 2)->comment('余额');
            $table->enum('status', ['open', 'partial', 'settled', 'voided'])->default('open')->comment('状态:未结/部分结算/已结账/已作废');
            $table->string('source_type', 50)->nullable()->comment('来源类型:sales_invoice/manual');
            $table->unsignedBigInteger('source_id')->nullable()->comment('来源单据ID');
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('aux_items')->onDelete('cascade');
            $table->index(['company_id', 'customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_bills');
    }
};
