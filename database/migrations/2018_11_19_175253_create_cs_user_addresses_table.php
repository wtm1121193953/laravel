<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsUserAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_user_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->string('province')->default('')->comment('省份');
            $table->integer('province_id')->default(0)->comment('省份id');
            $table->string('city')->default('')->comment('城市');
            $table->integer('city_id')->default(0)->comment('城市id');
            $table->string('area')->default('')->comment('县区');
            $table->integer('area_id')->default(0)->comment('县区id');
            $table->string('address',600)->default('')->comment('详细地址');
            $table->string('contacts',50)->default('')->comment('联系人');
            $table->string('contact_phone',20)->default('')->comment('联系电话');
            $table->tinyInteger('is_default')->default(0)->comment('是否默认地址 1是 0否');
            $table->timestamps();
            $table->comment = '用户地址表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_user_addresses');
    }
}
