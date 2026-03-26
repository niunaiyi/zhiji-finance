<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_settlements', function (Blueprint $table) {
            $table->comment('应收核销记录表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('ar_bill_id')->comment('应收账单ID');
            $table->unsignedBigInteger('ar_receipt_id')->comment('收款单ID');
            $table->decimal('amount', 15, 2)->comment('结算/核销金额');
            $table->timestamp('settled_at')->comment('结算时间');
            $table->unsignedBigInteger('settled_by')->comment('核销人');
            $table->timestamps();

            $table->foreign('ar_bill_id')->references('id')->on('ar_bills')->onDelete('cascade');
            $table->foreign('ar_receipt_id')->references('id')->on('ar_receipts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_settlements');
    }
};
