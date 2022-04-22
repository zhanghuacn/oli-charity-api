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
        Schema::create('auction_bid_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auction_id')->comment('拍卖商品ID');
            $table->decimal('price')->comment('上一轮竞拍价');
            $table->decimal('bid_price')->comment('当前举牌价');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
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
        Schema::dropIfExists('auction_bid_records');
    }
};
