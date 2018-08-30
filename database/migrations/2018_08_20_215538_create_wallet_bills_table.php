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
            $table->integer('wallet_id')->index()->default(0)->comment('钱包ID');
            $table->integer('origin_id')->index()->default(0)->comment('用户ID');
            $table->tinyInteger('origin_type')->index()->default(1)->comment('用户类型 1-用户 2-商户 3-运营中心');
            $table->string('bill_no')->index()->default('')->comment('流水单号');
            $table->tinyInteger('type')->index()->default(1)->comment('类型  1-个人消费返利  2-下级消费返利  3-个人消费返利退款  4-下级消费返利退款 5-运营中心交易分润 6-运营中心交易分润退款 7-提现 8-提现失败');
            $table->integer('obj_id')->index()->default(1)->comment('产生流水的来源ID, 返利相关为返利记录ID, 提现为提现记录ID');
            $table->tinyInteger('inout_type')->default(1)->comment('收支类型 1-收入 2-支出');
            $table->decimal('amount', 11, 2)->default(0)->comment('变动金额');
            $table->tinyInteger('amount_type')->default(1)->comment('变动金额类型, 1-冻结金额, 2-非冻结金额');
            $table->decimal('after_amount', 11, 2)->default(0)->comment('变动后账户余额（包含冻结金额）');
            $table->decimal('after_balance', 11, 2)->default(0)->comment('变动后可提现金额');
            $table->timestamps();

            $table->comment = '钱包流水表';
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
