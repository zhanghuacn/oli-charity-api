<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('机构');
            $table->unsignedBigInteger('activity_id')->comment('活动');
            $table->unsignedBigInteger('lottery_id')->comment('抽奖');
            $table->unsignedBigInteger('goods_id')->comment('商品');
            $table->string('name')->comment('名称');
            $table->string('description')->nullable()->comment('描述');
            $table->unsignedInteger('num')->comment('奖品数量');
            $table->json('winners')->nullable()->comment('中奖榜单');
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
        Schema::dropIfExists('prizes');
    }
}
