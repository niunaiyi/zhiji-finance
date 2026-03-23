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
            $table->foreignId('voucher_line_id')->constrained()->onDelete('cascade');
            $table->foreignId('aux_category_id')->constrained()->onDelete('restrict');
            $table->foreignId('aux_item_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_line_aux');
    }
};
