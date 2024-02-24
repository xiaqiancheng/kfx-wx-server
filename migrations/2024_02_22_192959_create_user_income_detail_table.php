<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserIncomeDetailTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_income_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('blogger_id')->comment('博主ID');
            $table->bigInteger('task_id')->default(0)->comment('任务ID');
            $table->integer('amount')->default(0)->comment('收入');
            $table->string('name')->default('')->comment('描述');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_income_detail');
    }
}
