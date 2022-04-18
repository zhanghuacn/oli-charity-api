<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('机构ID');
            $table->unsignedBigInteger('activity_id')->comment('活动ID');
            $table->string('name')->comment('拍卖品名称');
            $table->text('description')->nullable()->comment('拍卖品描述');
            $table->json('images')->nullable()->comment('拍卖图片');
            $table->decimal('price')->comment('起拍价格');
            $table->timestamp('start_time')->comment('拍卖开始时间');
            $table->timestamp('end_time')->comment('拍卖结束时间');
            $table->decimal('current_bid_price')->nullable()->comment('举牌价格');
            $table->unsignedBigInteger('current_bid_user_id')->nullable()->comment('当前举牌用户');
            $table->timestamp('current_bid_time')->nullable()->comment('举牌时间');
            $table->boolean('is_auction')->default(true)->comment('是否可竞拍');
            $table->string('receiver')->nullable()->comment('收货人');
            $table->string('receiver_address')->nullable()->comment('收货人地址');
            $table->string('receiver_phone')->nullable()->comment('收货人联系电话');
            $table->morphs('auctionable');
            $table->json('extends')->nullable()->comment('扩展字段');
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
        Schema::dropIfExists('auctions');
    }
};
