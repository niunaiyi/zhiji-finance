<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aux_categories', function (Blueprint $table) {
            $table->comment('辅助核算类别表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('code', 20)->comment('类别编码:customer/supplier/dept/employee/inventory/project');
            $table->string('name', 50)->comment('类别名称');
            $table->boolean('is_system')->default(false)->comment('系统内置不可删');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aux_categories');
    }
};
