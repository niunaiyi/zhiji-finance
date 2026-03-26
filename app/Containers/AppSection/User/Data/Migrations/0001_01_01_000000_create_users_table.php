<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table) {
            $table->comment('用户基础信息表');
            $table->id();
            $table->string('name')->nullable()->comment('用户名');
            $table->string('email')->unique()->nullable()->comment('电子邮箱');
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱验证时间');
            $table->string('password')->nullable()->comment('密码哈希');
            $table->boolean('is_super_admin')->default(false)->comment('是否超级管理员');
            $table->string('gender')->nullable()->comment('性别');
            $table->date('birth')->nullable()->comment('出生日期');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', static function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', static function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
