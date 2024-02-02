<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSystemRoles extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_roles', function (Blueprint $table) {
            $table->comment('角色');
            $table->bigIncrements('id');
            $table->string('name', 191)->unique()->default('')->comment('角色唯一标识');
            $table->string('display_name', 191)->default('')->comment('角色名称');
            $table->string('description', 191)->default('')->comment('描述');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_roles');
    }
}
