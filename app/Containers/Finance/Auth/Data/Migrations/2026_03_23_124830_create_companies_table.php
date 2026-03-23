<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->tinyInteger('fiscal_year_start')->default(1);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE companies ADD CONSTRAINT companies_fiscal_year_start_check CHECK (fiscal_year_start >= 1 AND fiscal_year_start <= 12)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
