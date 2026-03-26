<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_company_roles', function (Blueprint $table) {
            $table->comment('用户公司角色关联表');
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('company_id')->comment('公司/账套ID');
            $table->enum('role', ['admin', 'accountant', 'auditor', 'viewer'])->comment('角色:管理员/会计/审计/查看者');
            $table->boolean('is_active')->default(true)->comment('是否有效');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['user_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_company_roles');
    }
};
