<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('转账编号');
            $table->unsignedBigInteger('charity_id')->comment('机构');
            $table->unsignedBigInteger('activity_id')->comment('活动');
            $table->unsignedBigInteger('ticket_id')->comment('门票');
            $table->unsignedBigInteger('user_id')->comment('用户');
            $table->decimal('amount')->comment('转账金额');
            $table->enum('status', ['WAIT', 'PASSED', 'REFUSE'])->nullable()->comment('核验时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedBigInteger('reviewer')->nullable()->comment('审核人');
            $table->json('voucher')->nullable()->comment('凭证');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->timestamp('verified_at')->nullable()->comment('核验时间');
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
        Schema::dropIfExists('transfers');
    }
}
