<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('机构ID');
            $table->unsignedBigInteger('activity_id')->comment('活动ID');
            $table->string('name')->comment('礼品名称');
            $table->string('description')->nullable()->comment('礼品描述');
            $table->text('content')->nullable()->comment('礼品内容');
            $table->json('images')->nullable()->comment('图片');
            $table->json('extends')->nullable()->comment('扩展字段');
            $table->morphs('giftable');
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
        Schema::dropIfExists('gifts');
    }
}
