<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiniprogramScenesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('miniprogram_scenes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->default(0)->comment('运营中心ID');
            $table->string('page')->default('')->comment('小程序页面');
            $table->tinyInteger('type')->default(1)->comment('场景类型 1-小程序间支付跳转码');
            $table->string('payload', 5000)->default('')->comment('场景附带的参数, json格式, type为1时为 {order_no, user_id}');
            $table->timestamps();

            $table->comment = '小程序场景表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miniprogram_scenes');
    }
}
