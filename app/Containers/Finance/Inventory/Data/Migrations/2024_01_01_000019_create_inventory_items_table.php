<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->comment('存货档案表');
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('sku', 50)->comment('SKU/编码');
            $table->string('name', 100)->comment('物品名称');
            $table->string('unit', 20)->default('个')->comment('计量单位');
            $table->string('category', 50)->nullable()->comment('分类');
            $table->decimal('current_quantity', 15, 4)->default(0)->comment('当前库存量');
            $table->decimal('current_average_cost', 15, 4)->default(0)->comment('加权平均单价');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
