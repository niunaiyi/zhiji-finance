<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->decimal('opening_debit', 15, 2)->default(0)->comment('期初借方');
            $table->decimal('opening_credit', 15, 2)->default(0)->comment('期初贷方');
            $table->decimal('period_debit', 15, 2)->default(0)->comment('本期借方发生额');
            $table->decimal('period_credit', 15, 2)->default(0)->comment('本期贷方发生额');
            $table->decimal('closing_debit', 15, 2)->default(0)->comment('期末借方');
            $table->decimal('closing_credit', 15, 2)->default(0)->comment('期末贷方');
            $table->timestamps();

            $table->unique(['company_id', 'period_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
