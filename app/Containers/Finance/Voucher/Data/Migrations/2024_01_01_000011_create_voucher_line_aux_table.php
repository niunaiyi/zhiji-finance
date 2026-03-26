<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_line_aux', function (Blueprint $table) {
            $table->id();
            $table->comment('凭证分录辅助核算明细表');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('voucher_line_id')->comment('凭证分录ID');
            $table->unsignedBigInteger('aux_category_id')->comment('辅助核算类别ID');
            $table->unsignedBigInteger('aux_item_id')->comment('辅助核算项目ID');
            $table->timestamps();

            $table->foreign('voucher_line_id')->references('id')->on('voucher_lines')->onDelete('cascade');
            $table->foreign('aux_category_id')->references('id')->on('aux_categories')->onDelete('cascade');
            $table->foreign('aux_item_id')->references('id')->on('aux_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_line_aux');
    }
};
