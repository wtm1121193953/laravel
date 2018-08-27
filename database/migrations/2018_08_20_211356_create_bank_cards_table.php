<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('origin_id')->index()->comment('分润用户ID, 用户ID/商户ID/或运营中心ID');
            $table->tinyInteger('origin_type')->index()->default(1)->comment('分润用户类型, 1-用户 2-商户 3-运营中心');
            $table->tinyInteger('bank_card_type')->default(1)->comment('银行账户类型 1-公司账户 2-个人账户');
            $table->string('bank_card_open_name')->default('')->comment('银行开户名');
            $table->string('bank_card_no')->default('')->comment('银行账号');
            $table->string('bank_name')->default('')->comment('开户行');
            $table->string('sub_bank_name')->default('')->comment('开户支行名称');

            $table->timestamps();

            $table->comment = '银行卡表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_cards');
    }
}
