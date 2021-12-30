<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('慈善机构');
            $table->unsignedBigInteger('activity_id')->comment('活动');
            $table->unsignedBigInteger('user_id')->comment('用户');
            $table->enum('status', ['WAIT', 'PASSED', 'REFUSE'])->default('WAIT')->comment('审核状态:等待，通过，拒绝');
            $table->unsignedBigInteger('reviewer')->nullable()->comment('审核人');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamp('reviewed_at')->nullable()->comment('审核时间');
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
        Schema::dropIfExists('applies');
    }
}
