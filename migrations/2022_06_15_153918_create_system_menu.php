<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSystemMenu extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_menu', function (Blueprint $table) {
            $table->comment('菜单');
            $table->bigIncrements('id');
            $table->integer('system_permission_id')->comment('关联权限id，菜单是对应权限的');
            $table->integer('parent_id')->default(0)->comment('父级id');
            $table->string('name', 45)->default('')->comment('菜单名称');
            $table->string('icon', 45)->default('')->comment('展示icon');
            $table->string('path', 45)->default('')->comment('访问路由');
            $table->string('view', 255)->default('')->comment('文件路径');
            $table->tinyInteger('sort')->default(0)->comment('排序');
            $table->string('additional')->default('')->comment('附加字段');
            $table->string('description')->default('')->comment('菜单描述');
            $table->tinyInteger('status')->default(0)->comment('1:显示，2：隐藏');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_menu');
    }
}
