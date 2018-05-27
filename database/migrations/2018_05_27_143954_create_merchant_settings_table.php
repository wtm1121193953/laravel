<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('所属运营中心ID');
            $table->integer('merchant_id')->index()->default(0)->comment('商家ID');
            $table->string('key')->index()->default('')->comment('配置的键');
            $table->string('value')->default('')->comment('配置的值');
            $table->string('info')->default('')->comment('配置说明');
            $table->timestamps();
            $table->comment = '商户配置表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_settings');
    }
}
