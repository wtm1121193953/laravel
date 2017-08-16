<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectEnvironmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_environments', function (Blueprint $table) {
            $table->increments('id')->comment('主键ID');
            $table->integer('project_id')->index()->comment('项目ID');
            $table->string('name')->comment('环境名');
            $table->string('desc', 500)->default('')->comment('环境说明');
            $table->string('url', 500)->comment('该项目环境的基础地址(包括域名)');
            $table->string('path', 500)->comment('服务器部署路径');
            $table->integer('server_id')->index()->default(0)->comment('环境所在服务器ID');
            $table->string('script', 5000)->default('')->comment('环境发布脚本');
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
        Schema::dropIfExists('project_environments');
    }
}
