<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMessageNoticeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_notice', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('task_id')->default(0)->comment('任务ID');
            $table->bigInteger('blogger_id')->default(0)->comment('博主ID');
            $table->string('name')->default('')->comment('名称');
            $table->string('description')->default('')->comment('描述');
            $table->tinyInteger('status')->default(0)->comment('读取状态 0未读 1已读');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_notice');
    }
}
