<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_withdraws', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wallet_id')->index()->default(0)->comment('钱包ID');
            $table->integer('origin_id')->index()->default(0)->comment('用户ID');
            $table->tinyInteger('origin_type')->index()->default(1)->comment('用户类型 1-用户 2-商户 3-运营中心');
            $table->string('withdraw_no')->index()->default('')->comment('提现编号');
            $table->decimal('amount', 11, 2)->default(0)->comment('提现金额');
            $table->decimal('charge_amount', 11, 2)->default(0)->comment('手续费');
            $table->decimal('remit_amount', 11, 2)->default(0)->comment('打款金额');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-审核中 2-审核通过 3-已打款 4-打款失败 5-审核不通过');
            $table->string('invoice_express_company')->default('')->comment('发票快递公司');
            $table->string('invoice_express_no')->default('')->comment('发票快递编号');
            $table->tinyInteger('bank_card_type')->default(1)->comment('账户类型 1-公司 2-个人');
            $table->string('bank_card_open_name')->default('')->comment('银行卡开户名');
            $table->string('bank_card_no')->default('')->comment('银行卡号');
            $table->string('bank_name')->default('')->comment('开户行');
            $table->integer('batch_id')->index()->default(0)->comment('批次ID');
            $table->string('batch_no')->index()->default('')->comment('批次编号');
            $table->string('remark')->default('')->comment('备注');
            $table->timestamps();

            $table->comment = '提现记录表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_withdraws');
    }
}
