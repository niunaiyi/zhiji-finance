<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->tinyInteger('level');
            $table->enum('element_type', ['asset', 'liability', 'equity', 'income', 'expense', 'cost']);
            $table->enum('balance_direction', ['debit', 'credit']);
            $table->boolean('is_detail')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_aux')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'parent_id']);
            $table->index(['company_id', 'is_active', 'is_detail']);

            $table->foreign('parent_id')->references('id')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
