<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityApplyRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_apply_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_id')->comment('慈善机构')->constrained('charities');
            $table->foreignId('activity_id')->comment('活动')->constrained('activities');
            $table->foreignId('user_id')->comment('用户')->constrained('users');
            $table->enum('status', ['WAIT', 'PASSED', 'REFUSE'])->default('WAIT')->comment('审核状态:等待，通过，拒绝');
            $table->unsignedBigInteger('reviewer')->nullable()->comment('审核人');
            $table->unsignedBigInteger('remark')->nullable()->comment('备注');
            $table->timestamp('reviewed')->nullable()->comment('审核时间');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('activity_apply_records');
    }
}
