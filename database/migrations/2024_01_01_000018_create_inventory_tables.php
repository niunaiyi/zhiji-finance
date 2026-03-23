<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained('aux_items')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('qty', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'inventory_id', 'warehouse_id']);
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('trans_type', ['purchase_in', 'sales_out', 'transfer', 'adjust']);
            $table->foreignId('inventory_id')->constrained('aux_items')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('qty', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total_cost', 15, 2);
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->date('trans_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventories');
    }
};
