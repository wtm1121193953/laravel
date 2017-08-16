<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodeRepositoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_repositories', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->index()->comment('项目仓库类型: 1-svn 2-git(先只能用svn)');
            $table->string('path', 500)->comment('项目仓库地址');
            $table->string('username', 500)->comment('项目仓库用户名');
            $table->string('password', 500)->comment('项目仓库地址');
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
        Schema::dropIfExists('code_repositories');
    }
}
