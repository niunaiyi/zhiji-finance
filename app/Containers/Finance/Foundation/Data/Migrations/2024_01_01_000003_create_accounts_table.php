<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->comment('会计科目表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('code', 20)->comment('科目编码');
            $table->string('name', 100)->comment('科目名称');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('上级科目');
            $table->tinyInteger('level')->comment('级次');
            $table->enum('element_type', ['asset', 'liability', 'equity', 'income', 'expense', 'cost'])->comment('会计要素:资产/负债/所有者权益/收入/费用/成本');
            $table->enum('balance_direction', ['debit', 'credit'])->comment('余额方向:借/贷');
            $table->boolean('is_detail')->default(false)->comment('是否末级科目');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->boolean('has_aux')->default(false)->comment('是否启用辅助核算');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
