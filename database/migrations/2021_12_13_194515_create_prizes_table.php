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
            $table->foreignId('charity_id')->comment('慈善机构')->constrained('charities');
            $table->foreignId('activity_id')->comment('活动')->constrained('activities');
            $table->string('name')->comment('名称');
            $table->string('description')->nullable()->comment('描述');
            $table->unsignedInteger('num')->comment('奖品数量');
            $table->json('winner')->nullable()->comment('中奖榜单');
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
