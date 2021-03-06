<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailVerifyCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_verify_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->index()->comment('手机号');
            $table->string('verify_code')->index()->comment('验证码');
            $table->tinyInteger('type')->index()->comment('验证码类型 1-绑定tps账号验证码');
            $table->dateTime('expire_time')->comment('超时时间');
            $table->tinyInteger('status')->default(1)->index()->comment('状态 1-有效 2-无效');
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
        Schema::dropIfExists('email_verify_codes');
    }
}
