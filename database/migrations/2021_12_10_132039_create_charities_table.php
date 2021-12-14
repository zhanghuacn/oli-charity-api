<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('名称');
            $table->string('logo')->comment('logo');
            $table->string('backdrop')->comment('背景图');
            $table->string('website')->comment('网站');
            $table->string('description')->comment('描述');
            $table->text('introduce')->comment('描述');
            $table->unsignedInteger('staff_num')->comment('员工数量');
            $table->json('credentials')->nullable()->comment('证件');
            $table->json('documents')->nullable()->comment('其他文件');
            $table->string('contact')->comment('联系人');
            $table->string('phone')->comment('联系人电话');
            $table->string('mobile')->nullable()->comment('联系人座机');
            $table->string('email')->nullable()->comment('邮箱');
            $table->string('address')->nullable()->comment('地址');
            $table->string('stripe_account')->nullable()->comment('stripe管理账号');
            $table->boolean('is_visible')->default(true)->comment('是否可见');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->json('cache')->nullable()->comment('数据缓存');
            $table->enum('status', ['WAIT', 'PASSED', 'REFUSE'])->comment('审核状态:等待，通过，拒绝');
            $table->string('remark')->nullable()->comment('审核备注');
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
        Schema::dropIfExists('charities');
    }
}
