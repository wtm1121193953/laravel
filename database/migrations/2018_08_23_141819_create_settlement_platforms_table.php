<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlement_platforms', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('oper_id')->index()->comment('所属运营中心ID');
            $table->integer('merchant_id')->index()->comment('商家ID');
            $table->date('date')->index()->comment('结算的订单日期');
            $table->decimal('settlement_rate', 4, 2)->comment('结算费率');
            $table->decimal('amount')->comment('结算总金额');
            $table->decimal('charge_amount')->comment('手续费');
            $table->decimal('real_amount')->comment('商家实际收到的金额');

            // 打款信息
            $table->string('bank_open_name')->default('')->comment('银行开户名');
            $table->string('bank_card_no')->default('')->comment('银行账号');
            $table->string('sub_bank_name')->default('')->comment('开户支行名称');
            $table->string('bank_open_address')->default('')->comment('开户支行地址');

            $table->string('pay_pic_url')->default('')->comment('回款单图片');

            // 发票信息
            $table->string('invoice_title')->default('')->comment('发票抬头');
            $table->string('invoice_no')->default('')->comment('发票税号');
            $table->tinyInteger('invoice_type')->default(0)->comment('发票类型 0-未上传 1-电子发票 2-纸质发票');
            $table->string('invoice_pic_url')->default('')->comment('发票图片地址  电子发票有效');
            $table->string('logistics_name')->default('')->comment('发票邮寄物流公司  纸质发票有效');
            $table->string('logistics_no')->default('')->comment('发票邮寄物流单号  纸质发票有效');

            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-未打款 2-已打款 3-已到账 4-打款失败');
            $table->string('settlement_no')->index()->default('')->comment('结算单号')->after('id');
            $table->string('reason')->index()->default('')->comment('打款失败原因')->after('status');
            $table->integer('settlement_pay_batch_id')->index()->default(0)->comment('打款批次ID')->after('reason');
            $table->string('pay_batch_no')->index()->default('')->comment('打款批次号, 对应 settlement_pay_batches 表 batch_no 字段')->after('settlement_pay_batch_id');

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
        Schema::dropIfExists('settlement_platforms');
    }
}
