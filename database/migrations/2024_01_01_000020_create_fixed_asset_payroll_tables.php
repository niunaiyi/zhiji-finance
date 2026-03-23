<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('asset_no', 30);
            $table->string('name', 100);
            $table->string('category', 50);
            $table->date('purchase_date');
            $table->decimal('original_value', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('net_value', 15, 2);
            $table->enum('depreciation_method', ['straight_line', 'double_declining']);
            $table->integer('useful_life_months');
            $table->decimal('residual_rate', 5, 4);
            $table->enum('status', ['active', 'disposed'])->default('active');
            $table->timestamps();
        });

        Schema::create('depreciation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->decimal('depreciation_amount', 15, 2);
            $table->boolean('is_posted')->default(false);
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->string('payroll_no', 30);
            $table->date('payroll_date');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('aux_items')->onDelete('restrict');
            $table->foreignId('dept_id')->constrained('aux_items')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_lines');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('depreciation_schedules');
        Schema::dropIfExists('fixed_assets');
    }
};
