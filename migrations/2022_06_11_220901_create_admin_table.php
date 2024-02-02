<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 100)->comment('用户名');
            $table->string('account', 100)->comment('账号');
            $table->string('password', 255)->comment('密码');
            $table->string('avatar')->default('')->comment('头像');
            $table->char('mobile', 11)->default('')->comment('手机号');
            $table->string('email', 100)->default('')->comment('邮箱');
            $table->boolean('status')->default(true)->comment('账号状态');
            $table->dateTime('last_login_time')->nullable()->comment('最后登录时间');
            $table->string('last_login_ip', 100)->default('')->comment('最后登录ip');
            $table->timestamps();
            $table->index('account', 'index_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
}
