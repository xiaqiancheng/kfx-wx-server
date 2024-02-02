<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSystemPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_permissions', function (Blueprint $table) {
            $table->comment('权限');
            $table->bigIncrements('id');
            $table->integer('parent_id')->default(0)->comment('父级id');
            $table->string('name', 191)->unique()->default('')->comment('权限标识');
            $table->string('display_name', 191)->default('')->comment('显示权限名称');
            $table->string('effect_uri')->default('')->comment('管理的路由');
            $table->string('description', 191)->default('')->comment('权限简介');
            $table->tinyInteger('sort')->default(0)->comment('排序');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_permissions');
    }
}
