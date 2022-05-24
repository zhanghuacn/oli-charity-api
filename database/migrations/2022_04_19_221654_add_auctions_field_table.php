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
        Schema::table('auctions', function (Blueprint $table) {
            $table->string('thumb',1024)->nullable()->comment('缩略图');
            $table->json('keyword')->nullable()->comment('关键词');
            $table->text('content')->nullable()->comment('商品介绍');
            $table->json('trait')->nullable()->comment('商品特点');
            $table->boolean('is_receive')->default(false)->comment('是否领取');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn('votes');
            $table->dropColumn('keyword');
            $table->dropColumn('content');
            $table->dropColumn('trait');
        });
    }
};
