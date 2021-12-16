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
            $table->string('order_sn')->unique()->comment('订单编号');
            $table->enum('type', ['CHARITY', 'ACTIVITY', 'BAZAAR'])->comment('订单:机构捐赠，活动捐赠，义卖商品');
            $table->unsignedBigInteger('user_id')->comment('用户');
            $table->unsignedBigInteger('charity_id')->comment('机构');
            $table->string('currency')->comment('货币类型');
            $table->decimal('amount')->comment('付款金额');
            $table->decimal('fee_amount')->comment('手续费');
            $table->decimal('total_amount')->comment('实际到手金额');
            $table->string('payment_no')->unique()->comment('交易号');
            $table->enum('payment_type', ['ONLINE', 'OFFLINE'])->comment('支付类型');
            $table->enum('payment_method', ['STRIPE', 'BANK'])->comment('支付方式');
            $table->enum('payment_status', ['UNPAID', 'IN_PAYMENT', 'PAID', 'CLOSED'])->default('UNPAID')->comment('订单支付状态');
            $table->timestamp('payment_time')->nullable()->comment('支付时间');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->morphs('orderable');
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
