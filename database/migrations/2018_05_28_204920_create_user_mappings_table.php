<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('origin_id')->index()->default(0)->comment('运营中心或商户ID');
            $table->tinyInteger('origin_type')->default(0)->comment('1-商户 2-运营中心');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->timestamps();
            $table->comment = '用户与商户及运营中心映射';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_mappings');
    }
}
