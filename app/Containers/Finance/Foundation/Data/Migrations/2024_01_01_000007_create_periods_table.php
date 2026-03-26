<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->comment('会计期间表');
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->smallInteger('fiscal_year')->comment('会计年度');
            $table->tinyInteger('period_number')->comment('会计期间(1-12)');
            $table->date('start_date')->comment('开始日期');
            $table->date('end_date')->comment('结束日期');
            $table->enum('status', ['open', 'closed', 'locked'])->default('open')->comment('状态:开启/结账/锁定');
            $table->timestamp('closed_at')->nullable()->comment('结账时间');
            $table->unsignedBigInteger('closed_by')->nullable()->comment('结账人');
            $table->timestamps();

            $table->index(['company_id', 'fiscal_year', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
