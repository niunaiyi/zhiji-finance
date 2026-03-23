<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('ar_bill_id')->constrained()->onDelete('cascade');
            $table->foreignId('ar_receipt_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->timestamp('settled_at');
            $table->foreignId('settled_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_settlements');
    }
};
