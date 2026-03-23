<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->enum('voucher_type', ['receipt', 'payment', 'transfer']);
            $table->string('voucher_no', 20)->comment('格式：2024-记-0001');
            $table->date('voucher_date');
            $table->enum('status', ['draft', 'reviewed', 'posted', 'reversed', 'voided'])->default('draft');
            $table->string('summary', 200)->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->string('source_type', 50)->nullable()->comment('manual/purchase/sales/payroll/depreciation');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'period_id', 'status']);
            $table->index(['company_id', 'voucher_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
