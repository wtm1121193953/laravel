<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('所属运营中心ID');
            $table->integer('merchant_category_id')->index()->default(0)->comment('上架类别ID');
            $table->string('name')->default('')->comment('商家名称');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常 2-禁用');
            $table->decimal('lng', 15, 12)->default(0)->comment('商家所在经度');
            $table->decimal('lat', 15, 12)->default(0)->comment('商家所在纬度');
            $table->string('address')->default('')->comment('商家地址');
            $table->string('province')->default('')->comment('所在省份');
            $table->integer('province_id')->default(0)->comment('所在省份Id');
            $table->string('city')->default('')->comment('所在城市');
            $table->integer('city_id')->default(0)->comment('所在城市Id');
            $table->string('area')->default('')->comment('所在县区');
            $table->integer('area_id')->default(0)->comment('所在县区Id');
            $table->timestamps();

            $table->comment = '商家表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants');
    }
}
