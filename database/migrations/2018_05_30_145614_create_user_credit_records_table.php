<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCreditRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_credit_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->integer('credit')->default(0)->comment('产生积分[=订单利润 * 返利比例 * 积分系数]');
            $table->tinyInteger('inout_type')->index()->default(1)->comment('收支类型(1-收入 2-支出)');
            $table->tinyInteger('type')->index()->default(1)->comment('来源类型(1-自返 2-分享提成 3-商家提成 4-退款退回 5-消费支出)');
            $table->tinyInteger('user_level')->default(1)->comment('产生积分时的用户等级');
            $table->tinyInteger('merchant_level')->default(1)->comment('积分产生时的商家等级(类型为商家提成时存在)');
            $table->string('order_no', 50)->index()->default('')->comment('关联订单号(类型为消费支出时为积分订单号, 其他为正常的订单号)');
            $table->string('consume_user_mobile', 11)->default('')->comment('消费用户手机号');
            $table->decimal('order_profit_amount')->default(0)->comment('订单利润');
            $table->decimal('ratio', 4, 2)->default(0)->comment('返利比例[=用户等级对应比例 * 用户所关联的商户等级加成(若存在), 是一个百分比, 如20则表示百分之20]');
            $table->decimal('credit_multiplier_of_amount')->default(1)->comment('积分系数, 系统配置项, 用于记录积分生成时配置的值, 用于订单金额与积分之间的换算');
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
        Schema::dropIfExists('user_credit_records');
    }
}
