<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('机构');
            $table->string('title')->comment('活动标题');
            $table->string('description')->nullable()->comment('描述');
            $table->text('content')->nullable()->comment('活动内容');
            $table->string('location')->comment('活动地点');
            $table->timestamp('begin_time')->comment('活动开始时间');
            $table->timestamp('end_time')->comment('活动结束时间');
            $table->boolean('is_visible')->default(true)->comment('是否可见');
            $table->boolean('is_private')->default(false)->comment('是否私有');
            $table->json('images')->nullable()->comment('活动图片');
            $table->json('settings')->nullable()->comment('活动设置');
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
        Schema::dropIfExists('activities');
    }
}
