<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_bills', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bill_no')->index()->default('')->comment('流水单号');
            $table->tinyInteger('type')->index()->default(1)->comment('类型  1-个人消费返利  2-下级消费返利 3-提现  4-个人消费返利退款  5-下级消费返利退款 6-运营中心交易分润 7-运营中心交易分润退款');
            $table->tinyInteger('inout_type')->default(1)->comment('收支类型 1-收入 2-支出');
            $table->decimal('amount', 11, 2)->default(0)->comment('变动金额');
            $table->tinyInteger('amount_type')->default(1)->comment('变动金额类型, 1-冻结金额, 2-非冻结金额');
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
        Schema::dropIfExists('wallet_bills');
    }
}
