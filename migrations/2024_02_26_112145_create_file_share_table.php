<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateFileShareTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_share', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 32)->comment('Token');
            $table->string('link')->default('')->comment('分享链接');
            $table->dateTime('expiration_time')->comment('到期时间');
            $table->tinyInteger('valid_time')->default(0)->comment('有效时间 0七天 1永久');
            $table->char('extracted_code', 6)->default('')->comment('提取码');
            $table->longText('files')->comment('文件');
            $table->string('download_code', 32)->default('')->comment('下载码');
            $table->timestamps();
            $table->index('token', 'index_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_share');
    }
}
