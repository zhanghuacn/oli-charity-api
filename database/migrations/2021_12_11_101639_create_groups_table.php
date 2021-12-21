<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charity_id')->comment('机构');
            $table->unsignedBigInteger('activity_id')->comment('活动');
            $table->string('name')->comment('名称');
            $table->string('description')->nullable()->comment('描述');
            $table->unsignedInteger('num')->nullable()->comment('团队人数限制');
            $table->unsignedBigInteger('owner_id')->nullable()->comment('创建人');
            $table->json('extends')->nullable()->comment('扩展信息');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('group_ticket', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->comment('团队');
            $table->unsignedBigInteger('ticket_id')->unique()->comment('门票');
        });

        Schema::create('group_invite', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->unique()->comment('门票');
            $table->unsignedBigInteger('inviter_id')->comment('邀请人');
            $table->unsignedBigInteger('group_id')->comment('团队');
            $table->enum('type', ['INVITE', 'REQUEST'])->comment('类型');
            $table->string('accept_token')->comment('接受token');
            $table->string('deny_token')->comment('拒绝token');
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
        Schema::dropIfExists('groups');
        Schema::dropIfExists('group_ticket');
        Schema::dropIfExists('group_invite');
    }
}
