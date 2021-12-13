<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_id')->comment('机构')->constrained('charities');
            $table->foreignId('activity_id')->comment('活动')->constrained('activities');
            $table->foreignId('user_id')->comment('创建人')->constrained('users');
            $table->string('name')->comment('名称');
            $table->string('description')->nullable()->comment('描述');
            $table->unsignedInteger('num')->comment('团队人数限制');
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
        Schema::dropIfExists('teams');
    }
}
