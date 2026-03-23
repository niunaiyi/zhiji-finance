<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->string('receipt_no', 30);
            $table->date('receipt_date');
            $table->foreignId('supplier_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_receipt_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('qty', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total_cost', 15, 2);
            $table->timestamps();
        });

        Schema::create('sales_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->string('shipment_no', 30);
            $table->date('shipment_date');
            $table->foreignId('customer_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('sale_amount', 15, 2);
            $table->decimal('cost_amount', 15, 2);
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_shipment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('qty', 15, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('sale_amount', 15, 2);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('cost_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_shipment_lines');
        Schema::dropIfExists('sales_shipments');
        Schema::dropIfExists('purchase_receipt_lines');
        Schema::dropIfExists('purchase_receipts');
    }
};
