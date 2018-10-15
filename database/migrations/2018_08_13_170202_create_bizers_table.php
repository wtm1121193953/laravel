<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBizersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bizers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',128)->default('')->comment('业务员姓名');
            $table->string('mobile', 11)->unique()->comment('手机号');
            $table->string('password', 64)->comment('密码');
            $table->string('salt', 64)->default('')->comment('盐值');
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
        Schema::dropIfExists('bizers');
    }
}
