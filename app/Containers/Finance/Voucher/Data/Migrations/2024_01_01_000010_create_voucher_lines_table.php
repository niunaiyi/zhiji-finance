<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('voucher_lines', function (Blueprint $table) {
            $table->comment('凭证分录表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('voucher_id')->comment('凭证ID');
            $table->tinyInteger('line_no')->comment('行号');
            $table->unsignedBigInteger('account_id')->comment('科目ID');
            $table->string('summary', 200)->nullable()->comment('摘要');
            $table->decimal('debit', 15, 2)->default(0)->comment('借方金额');
            $table->decimal('credit', 15, 2)->default(0)->comment('贷方金额');
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_lines');
    }
};
