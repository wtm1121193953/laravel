<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTpsBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tps_binds', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('origin_type')->index()->default(0)->comment('绑定的用户类型，1：用户，2：商户，3：运营中心。');
            $table->integer('origin_id')->index()->default(0)->comment('用户ID, 用户的是users表ID, 商户是merchants表ID, 运营中心是oper表ID');
            $table->string('tps_account')->default('')->comment('绑定的TPS账号');
            $table->timestamps();

            $table->comment = 'TPS账号绑定表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tps_binds');
    }
}
