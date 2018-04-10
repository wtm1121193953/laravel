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
            $table->string('province_name')->default('')->comment('商家所在省份');
            $table->string('province_id')->default('')->comment('商家所在省份Id');
            $table->string('city_name')->default('')->comment('商家所在城市');
            $table->string('city_id')->default('')->comment('商家所在城市Id');
            $table->string('area_name')->default('')->comment('商家所在县区');
            $table->string('area_id')->default('')->comment('商家所在县区Id');
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
