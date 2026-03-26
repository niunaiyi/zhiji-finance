<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->comment('库存余额表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('inventory_id')->comment('存货项目ID(对应aux_items.id)');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID(对应aux_items.id)');
            $table->decimal('qty', 15, 4)->default(0)->comment('库存数量');
            $table->decimal('unit_cost', 15, 4)->default(0)->comment('单位成本(移动加权/FIFO)');
            $table->decimal('total_cost', 15, 2)->default(0)->comment('总成本');
            $table->timestamps();

            $table->foreign('inventory_id')->references('id')->on('aux_items')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('aux_items')->onDelete('cascade');
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->comment('库存交易流水表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->enum('trans_type', ['purchase_in', 'sales_out', 'transfer', 'adjust'])->comment('交易类型:采购入库/销售出库/调拨/调整');
            $table->unsignedBigInteger('inventory_id')->comment('存货项目ID');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->decimal('qty', 15, 4)->comment('交易数量(正数为入，负数为出)');
            $table->decimal('unit_cost', 15, 4)->comment('交易单价');
            $table->decimal('total_cost', 15, 2)->comment('交易总额');
            $table->string('source_type', 50)->nullable()->comment('来源单据类型');
            $table->unsignedBigInteger('source_id')->nullable()->comment('来源单据ID');
            $table->date('trans_date')->comment('交易日期');
            $table->timestamps();

            $table->foreign('inventory_id')->references('id')->on('aux_items')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('aux_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventories');
    }
};
