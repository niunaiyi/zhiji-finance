<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ap_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->string('bill_no', 30);
            $table->date('bill_date');
            $table->foreignId('supplier_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->decimal('settled_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->enum('status', ['open', 'partial', 'settled', 'voided'])->default('open');
            $table->boolean('is_estimate')->default(false);
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'supplier_id', 'status']);
        });

        Schema::create('ap_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->string('payment_no', 30);
            $table->date('payment_date');
            $table->foreignId('supplier_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->decimal('settled_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->enum('status', ['open', 'partial', 'settled'])->default('open');
            $table->timestamps();
        });

        Schema::create('ap_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('ap_bill_id')->constrained()->onDelete('cascade');
            $table->foreignId('ap_payment_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->timestamp('settled_at');
            $table->foreignId('settled_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_settlements');
        Schema::dropIfExists('ap_payments');
        Schema::dropIfExists('ap_bills');
    }
};
