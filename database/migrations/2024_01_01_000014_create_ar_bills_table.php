<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->string('bill_no', 30);
            $table->date('bill_date');
            $table->foreignId('customer_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->decimal('settled_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->enum('status', ['open', 'partial', 'settled', 'voided'])->default('open');
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_bills');
    }
};
