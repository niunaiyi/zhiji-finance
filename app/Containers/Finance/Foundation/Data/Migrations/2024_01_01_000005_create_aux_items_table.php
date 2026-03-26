<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aux_items', function (Blueprint $table) {
            $table->comment('辅助核算明细项表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('aux_category_id')->comment('归属辅助核算类别');
            $table->string('code', 50)->comment('核算项目代码');
            $table->string('name', 100)->comment('核算项目名称');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('支持层级');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->jsonb('extra')->nullable()->comment('扩展字段');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('aux_category_id')->references('id')->on('aux_categories')->onDelete('cascade');
            $table->unique(['company_id', 'aux_category_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aux_items');
    }
};
