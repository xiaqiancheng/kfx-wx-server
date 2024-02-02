<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSystemRolesPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_roles_permissions', function (Blueprint $table) {
            $table->comment('用户角色权限表');
            $table->bigIncrements('id');
            $table->integer('system_role_id')->unsigned()->comment('角色id');
            $table->integer('system_permission_id')->unsigned()->comment('权限id');
            $table->timestamps();
            $table->index('system_role_id', 'index_system_role_id');
            $table->index('system_permission_id', 'index_system_permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_roles_permissions');
    }
}
