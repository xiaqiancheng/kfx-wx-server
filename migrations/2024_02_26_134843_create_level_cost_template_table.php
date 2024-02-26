<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateLevelCostTemplateTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('level_cost_template', function (Blueprint $table) {
            $table->tinyInteger('template_id')->comment('模板ID');
            $table->tinyInteger('level_id')->comment('级别ID');
            $table->integer('cost')->default(0)->comment('费用（分）');
            $table->primary(['template_id', 'level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_cost_template');
    }
}
