<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBossUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boss_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('password', 64)->comment('密码');
            $table->string('salt', 64)->default('')->comment('盐值');
            $table->integer('group_id')->default(0)->comment('所属分组');
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
        Schema::dropIfExists('boss_users');
    }
}
