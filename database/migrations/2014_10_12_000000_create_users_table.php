<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Jialeo\LaravelSchemaExtend\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('用户名');
            $table->string('mobile')->index()->index()->default('')->comment('手机号');
            $table->string('email')->index()->default('')->comment('email');
            $table->string('account')->index()->default('')->comment('用户账号');
            $table->string('password')->default('')->comment('密码');
            $table->string('salt')->default('')->comment('盐值');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常 2-禁用');
            $table->timestamps();

            $table->comment = '用户表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
