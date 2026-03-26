<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_aux', function (Blueprint $table) {
            $table->comment('辅助核算余额表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('period_id')->comment('会计期间ID');
            $table->unsignedBigInteger('account_id')->comment('科目ID');
            $table->unsignedBigInteger('aux_category_id')->comment('辅助核算类别ID');
            $table->unsignedBigInteger('aux_item_id')->comment('辅助核算项目ID');
            $table->decimal('opening_debit', 15, 2)->default(0)->comment('期初借方');
            $table->decimal('opening_credit', 15, 2)->default(0)->comment('期初贷方');
            $table->decimal('period_debit', 15, 2)->default(0)->comment('本期借方发生额');
            $table->decimal('period_credit', 15, 2)->default(0)->comment('本期贷方发生额');
            $table->decimal('closing_debit', 15, 2)->default(0)->comment('期末借方');
            $table->decimal('closing_credit', 15, 2)->default(0)->comment('期末贷方');
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('aux_category_id')->references('id')->on('aux_categories')->onDelete('cascade');
            $table->foreign('aux_item_id')->references('id')->on('aux_items')->onDelete('cascade');
            $table->unique(['company_id', 'period_id', 'account_id', 'aux_category_id', 'aux_item_id'], 'balance_aux_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_aux');
    }
};
