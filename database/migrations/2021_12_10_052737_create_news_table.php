<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('名称');
            $table->string('thumb')->nullable()->comment('缩略图');
            $table->string('keyword')->nullable()->comment('关键词');
            $table->string('source')->nullable()->comment('来源');
            $table->string('description')->nullable()->comment('摘要');
            $table->text('content')->nullable()->comment('内容');
            $table->enum('status', ['ENABLE', 'DISABLE'])->default('ENABLE')->comment('状态: 上架,下架');
            $table->morphs('newsable');
            $table->timestamp('published_at')->comment('发布时间');
            $table->unsignedInteger('sort')->default(0)->comment('排序');
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
        Schema::dropIfExists('news');
    }
}
