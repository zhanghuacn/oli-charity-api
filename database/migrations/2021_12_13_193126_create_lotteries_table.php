<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLotteriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lotteries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('慈善机构');
            $table->unsignedBigInteger('activity_id')->comment('活动');
            $table->string('name')->comment('名称');
            $table->string('description')->nullable()->comment('描述');
            $table->json('images')->nullable()->comment('图片');
            $table->timestamp('begin_time')->comment('开始时间');
            $table->timestamp('end_time')->comment('结束时间');
            $table->decimal('standard_amount')->comment('达标金额');
            $table->timestamp('draw_time')->comment('开奖时间');
            $table->json('extends')->nullable()->comment('扩展信息');
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
        Schema::dropIfExists('lotteries');
    }
}
