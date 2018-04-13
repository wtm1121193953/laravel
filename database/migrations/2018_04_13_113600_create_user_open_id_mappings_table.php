<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserOpenIdMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_open_id_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('用户所属运营中心ID');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->string('open_id')->index()->default('')->comment('关联的微信小程序openId');
            $table->timestamps();

            $table->comment = '用户与openId关联关系表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_open_id_mappings');
    }
}
