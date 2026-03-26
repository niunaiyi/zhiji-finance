<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ap_bills', function (Blueprint $table) {
            $table->comment('应付账单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->string('bill_no', 30)->comment('账单编号');
            $table->date('bill_date')->comment('账单日期');
            $table->unsignedBigInteger('supplier_id')->comment('供应商ID(对应aux_items.id)');
            $table->decimal('amount', 15, 2)->comment('原始金额');
            $table->decimal('settled_amount', 15, 2)->default(0)->comment('已结算金额');
            $table->decimal('balance', 15, 2)->comment('余额');
            $table->enum('status', ['open', 'partial', 'settled', 'voided'])->default('open')->comment('状态:未结/部分结算/已结账/已作废');
            $table->boolean('is_estimate')->default(false)->comment('是否暂估');
            $table->string('source_type', 50)->nullable()->comment('来源类型');
            $table->unsignedBigInteger('source_id')->nullable()->comment('来源单据ID');
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('aux_items')->onDelete('cascade');
            $table->index(['company_id', 'supplier_id', 'status']);
        });

        Schema::create('ap_payments', function (Blueprint $table) {
            $table->comment('付款单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->string('payment_no', 30)->comment('付款单号');
            $table->date('payment_date')->comment('付款日期');
            $table->unsignedBigInteger('supplier_id')->comment('供应商ID');
            $table->decimal('amount', 15, 2)->comment('付款金额');
            $table->decimal('settled_amount', 15, 2)->default(0)->comment('已核销金额');
            $table->decimal('balance', 15, 2)->comment('余额');
            $table->enum('status', ['open', 'partial', 'settled'])->default('open')->comment('状态:未核销/部分核销/已结清');
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('aux_items')->onDelete('cascade');
        });

        Schema::create('ap_settlements', function (Blueprint $table) {
            $table->comment('应付核销记录表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('ap_bill_id')->comment('应付账单ID');
            $table->unsignedBigInteger('ap_payment_id')->comment('付款单ID');
            $table->decimal('amount', 15, 2)->comment('核销金额');
            $table->timestamp('settled_at')->comment('核销时间');
            $table->unsignedBigInteger('settled_by')->comment('核销人');
            $table->timestamps();

            $table->foreign('ap_bill_id')->references('id')->on('ap_bills')->onDelete('cascade');
            $table->foreign('ap_payment_id')->references('id')->on('ap_payments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_settlements');
        Schema::dropIfExists('ap_payments');
        Schema::dropIfExists('ap_bills');
    }
};
