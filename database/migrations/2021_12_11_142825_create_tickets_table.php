<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique()->comment('门票编码');
            $table->string('lottery_code')->nullable()->comment('彩票编号');
            $table->unsignedBigInteger('charity_id')->comment('慈善机构');
            $table->unsignedBigInteger('activity_id')->comment('活动');
            $table->unsignedBigInteger('user_id')->comment('用户');
            $table->unsignedBigInteger('group_id')->nullable()->comment('当前团队');
            $table->string('table_num')->nullable()->comment('桌号');
            $table->enum('type', ['DONOR', 'STAFF', 'SPONSOR'])->default('DONOR')->comment('门票类型: 普通 工作人员 赞助商');
            $table->unsignedDecimal('price')->comment('门票价格');
            $table->unsignedDecimal('amount')->comment('捐款总额');
            $table->boolean('anonymous')->default(false)->comment('是否匿名捐款');
            $table->timestamp('verified_at')->nullable()->comment('核销时间');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->unique(['activity_id', 'user_id']);
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
        Schema::dropIfExists('tickets');
    }
}
