<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateBloggerBusinessCardTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blogger_business_card', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('blogger_id')->comment('博主ID');
            $table->string('url')->default('')->comment('主页链接');
            $table->string('douyin_id')->default('')->comment('抖音ID');
            $table->string('nickname')->default('')->comment('昵称');
            $table->integer('fans_count')->default(0)->comment('粉丝数');
            $table->integer('digg_count')->default(0)->comment('点赞数');
            $table->integer('level_id')->default(0)->comment('等级');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogger_business_card');
    }
}
