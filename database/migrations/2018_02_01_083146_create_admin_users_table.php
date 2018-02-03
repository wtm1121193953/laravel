<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('password', 64)->comment('密码');
            $table->string('salt', 64)->default('')->comment('盐值');
            $table->integer('group_id')->default(0)->comment('所属分组');
            $table->tinyInteger('super')->default(2)->comment('是否是超级用户 1-超级用户 2-普通用户');
            $table->tinyInteger('status')->default(1)->comment('状态 1-正常  2-禁用');
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
        Schema::dropIfExists('admin_users');
    }
}
