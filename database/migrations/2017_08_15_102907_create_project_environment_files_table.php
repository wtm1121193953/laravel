<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectEnvironmentFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_environment_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->comment('项目ID');
            $table->integer('project_environment_id')->comment('环境ID');
            $table->string('name', 500)->comment('文件名');
            $table->string('path', 500)->default('')->comment('文件路径(相对于项目根目录的路径, 为空表示在项目根目录下)');
            $table->string('content', 500)->default('')->comment('文件内容');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_environment_files');
    }
}
