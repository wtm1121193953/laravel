<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserIdentitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_identities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->string('name')->default('')->comment('姓名');
            $table->string('number')->index()->default('')->comment('身份证号码');
            $table->string('front_pic')->comment('身份证正面照');
            $table->string('opposite_pic')->comment('身份证反面照');
            $table->tinyInteger('status')->default(1)->comment('状态 1：为正常，2：为停用');
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
        Schema::dropIfExists('user_identities');
    }
}
