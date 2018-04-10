<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('所属运营中心ID');
            $table->integer('merchant_id')->index()->default(0)->comment('商家ID');
            $table->string('name')->default('')->comment('用户名');
            $table->string('account')->index()->default('')->comment('用户账号');
            $table->string('mobile')->index()->default('')->comment('手机号');
            $table->string('email')->index()->default('')->comment('email');
            $table->string('password')->default('')->comment('密码');
            $table->string('salt')->default('')->comment('盐值');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常 2-禁用');
            $table->timestamps();

            $table->comment = '商家账号表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_accounts');
    }
}
