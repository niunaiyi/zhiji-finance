<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_receipts', function (Blueprint $table) {
            $table->comment('收款单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->string('receipt_no', 30)->comment('收款单号');
            $table->date('receipt_date')->comment('收款日期');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->decimal('amount', 15, 2)->comment('收款金额');
            $table->decimal('settled_amount', 15, 2)->default(0)->comment('已核销金额');
            $table->decimal('balance', 15, 2)->comment('剩余未核销金额');
            $table->enum('status', ['open', 'partial', 'settled'])->default('open')->comment('状态:未核销/部分核销/已全额核销');
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('aux_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_receipts');
    }
};
