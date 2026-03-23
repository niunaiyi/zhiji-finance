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
        Schema::create('account_aux_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('aux_category_id');
            $table->boolean('is_required')->default(true);
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['account_id', 'aux_category_id']);

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('restrict');

            $table->foreign('aux_category_id')
                ->references('id')
                ->on('aux_categories')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_aux_categories');
    }
};
