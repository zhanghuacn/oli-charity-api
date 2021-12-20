<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->string('username')->unique()->comment('用户名');
            $table->string('email')->unique()->comment('邮箱');
            $table->string('password')->comment('密码');
            $table->string('avatar')->nullable()->comment('头像');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->ipAddress('last_ip')->nullable()->comment('头像');
            $table->timestamp('last_active_at')->nullable()->comment('最后活跃时间');
            $table->timestamp('frozen_at')->nullable()->comment('冻结时间');
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
        Schema::dropIfExists('admins');
    }
}
