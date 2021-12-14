<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('用户')->constrained('users');
            $table->foreignId('charity_id')->comment('机构')->constrained('charities');
            $table->string('order_sn')->unique()->comment('订单编号');
            $table->string('transaction_id')->unique()->comment('交易号');
            $table->enum('pay_channel', ['STRIPE', 'BANK'])->comment('支付方式');
            $table->string('currency')->comment('货币类型');
            $table->decimal('pay_amount')->comment('付款金额');
            $table->decimal('amount')->comment('实际支付金额');
            $table->decimal('pay_fee')->comment('手续费');
            $table->enum('status', ['UNFINISHED', 'FINISHED', 'ERROR'])->default('UNFINISHED')->comment('订单状态');
            $table->timestamp('pay_time')->nullable()->comment('支付时间');
            $table->json('receipt')->nullable()->comment('线下支付凭证');
            $table->text('remark')->nullable()->comment('备注');
            $table->morphs('sourceable');
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
        Schema::dropIfExists('orders');
    }
}
