<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_aux_categories', function (Blueprint $table) {
            $table->comment('科目辅助核算类别对照表');
            $table->unsignedBigInteger('account_id')->comment('科目ID');
            $table->unsignedBigInteger('aux_category_id')->comment('辅助核算类别ID');
            $table->boolean('is_required')->default(false)->comment('是否必填');
            $table->tinyInteger('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            $table->primary(['account_id', 'aux_category_id']);
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('aux_category_id')->references('id')->on('aux_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_aux_categories');
    }
};
