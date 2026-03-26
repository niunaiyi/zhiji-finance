<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchase tables
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->comment('采购订单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('order_no', 30)->comment('订单编号');
            $table->date('order_date')->comment('订单日期');
            $table->unsignedBigInteger('supplier_id')->comment('供应商ID');
            $table->enum('status', ['draft', 'approved', 'received', 'closed'])->default('draft')->comment('状态:草稿/已审批/已收货/已关闭');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('aux_items')->onDelete('cascade');
        });

        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->comment('采购收货单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('receipt_no', 30)->comment('收货单号');
            $table->date('receipt_date')->comment('收货日期');
            $table->unsignedBigInteger('supplier_id')->comment('供应商ID');
            $table->enum('status', ['draft', 'posted'])->default('draft')->comment('状态:草稿/已过账');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('aux_items')->onDelete('cascade');
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->comment('采购发票表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('invoice_no', 30)->comment('发票编号');
            $table->date('invoice_date')->comment('发票日期');
            $table->unsignedBigInteger('supplier_id')->comment('供应商ID');
            $table->enum('status', ['draft', 'posted'])->default('draft')->comment('状态:草稿/已过账');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('aux_items')->onDelete('cascade');
        });

        // Sales tables
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->comment('销售订单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('order_no', 30)->comment('订单编号');
            $table->date('order_date')->comment('订单日期');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->enum('status', ['draft', 'approved', 'shipped', 'closed'])->default('draft')->comment('状态:草稿/已审批/已发货/已关闭');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('aux_items')->onDelete('cascade');
        });

        Schema::create('sales_shipments', function (Blueprint $table) {
            $table->comment('销售出库单表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('shipment_no', 30)->comment('出库单号');
            $table->date('shipment_date')->comment('发送日期');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->enum('status', ['draft', 'posted'])->default('draft')->comment('状态:草稿/已过账');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('aux_items')->onDelete('cascade');
        });

        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->comment('销售发票表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('invoice_no', 30)->comment('销售发票编号');
            $table->date('invoice_date')->comment('发票日期');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->enum('status', ['draft', 'posted'])->default('draft')->comment('状态:草稿/已过账');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('aux_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('sales_shipments');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('purchase_orders');
    }
};
