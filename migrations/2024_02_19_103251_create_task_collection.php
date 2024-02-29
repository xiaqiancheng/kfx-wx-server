<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTaskCollection extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_collection', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('task_id')->comment('任务ID');
            $table->bigInteger('blogger_id')->comment('博主ID');
            $table->bigInteger('shop_id')->default(0)->comment('店铺ID');
            $table->string('shop_name')->default('')->comment('店铺名称');
            $table->dateTime('reserve_time')->comment('预约时间');
            $table->integer('extra_cost')->default(0)->comment('额外费用（分）');
            $table->string('remark')->default('')->comment('备注');
            $table->tinyInteger('status')->default(0)->comment('审核状态 0待审核，1已审核，2审核未通过');
            $table->tinyInteger('level')->default(0)->comment('博主等级');
            $table->integer('fans_count')->default(0)->comment('粉丝数');
            $table->timestamps();
            $table->comment('任务领取表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_collection');
    }
}
