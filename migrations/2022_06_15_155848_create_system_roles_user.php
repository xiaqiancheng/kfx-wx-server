<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSystemRolesUser extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_roles_user', function (Blueprint $table) {
            $table->comment('角色关联用户表');
            $table->integer('system_role_id')->unsigned()->comment('角色id');
            $table->bigInteger('user_id')->comment('用户id');
            $table->timestamps();
            $table->primary(['system_role_id', 'user_id']);
            $table->index('system_role_id', 'index_system_role_id');
            $table->index('user_id', 'index_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_roles_user');
    }
}
