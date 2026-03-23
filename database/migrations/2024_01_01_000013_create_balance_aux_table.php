<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_aux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('aux_category_id')->constrained()->onDelete('restrict');
            $table->foreignId('aux_item_id')->constrained()->onDelete('restrict');
            $table->decimal('opening_debit', 15, 2)->default(0);
            $table->decimal('opening_credit', 15, 2)->default(0);
            $table->decimal('period_debit', 15, 2)->default(0);
            $table->decimal('period_credit', 15, 2)->default(0);
            $table->decimal('closing_debit', 15, 2)->default(0);
            $table->decimal('closing_credit', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'period_id', 'account_id', 'aux_category_id', 'aux_item_id'], 'balance_aux_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_aux');
    }
};
