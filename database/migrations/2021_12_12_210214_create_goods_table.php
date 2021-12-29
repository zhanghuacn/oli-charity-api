<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('机构');
            $table->unsignedBigInteger('activity_id')->comment('活动');
            $table->string('name')->comment('商品名称');
            $table->string('description')->nullable()->comment('商品描述');
            $table->text('content')->nullable()->comment('商品内容');
            $table->decimal('price')->comment('价格');
            $table->unsignedInteger('stock')->comment('库存数量');
            $table->json('images')->comment('商品图片');
            $table->json('cache')->nullable()->comment('数据缓存');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->morphs('goodsable');
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
        Schema::dropIfExists('goods');
    }
}
