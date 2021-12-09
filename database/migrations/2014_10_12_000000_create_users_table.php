<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->string('username')->unique()->comment('用户名');
            $table->string('email')->unique()->comment('邮箱');
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱认证时间');
            $table->string('password')->comment('密码');
            $table->string('avatar')->nullable()->comment('头像');
            $table->string('profile')->nullable()->comment('简介');
            $table->string('first_name')->nullable()->comment('名');
            $table->string('middle_name')->nullable()->comment('中间名');
            $table->string('last_name')->nullable()->comment('性');
            $table->enum('gender', ['UNKNOWN', 'FEMALE', 'MALE'])->default('unknown')->comment('性别：未知/女/男');
            $table->string('phone')->unique()->nullable()->comment('电话');
            $table->date('birthday')->nullable()->comment('生日');
            $table->enum('status', ['INACTIVATED', 'ACTIVE', 'FROZEN'])->default('INACTIVATED')->comment('状态：不活跃/激活/冻结');
            $table->json('cache')->nullable()->comment('数据缓存');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->json('settings')->nullable()->comment('用户设置');
            $table->boolean('is_visible')->default(true)->comment('是否可见');
            $table->timestamp('first_active_at')->nullable()->comment('首次活跃时间');
            $table->timestamp('last_active_at')->nullable()->comment('最后活跃时间');
            $table->timestamp('frozen_at')->nullable()->comment('冻结时间');
            $table->timestamp('status_remark')->nullable()->comment('状态说明');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
