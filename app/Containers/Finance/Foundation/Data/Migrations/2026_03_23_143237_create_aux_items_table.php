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
        Schema::create('aux_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('aux_category_id');
            $table->string('code', 50);
            $table->string('name', 100);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('extra')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'aux_category_id', 'code']);
            $table->index(['company_id', 'aux_category_id', 'is_active']);
            $table->index(['company_id', 'aux_category_id', 'parent_id']);

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
        Schema::dropIfExists('aux_items');
    }
};
