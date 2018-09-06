<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletConsumeQuotaRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_consume_quota_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wallet_id')->index()->default(0)->comment('钱包ID');
            $table->string('consume_quota_no')->index()->default('')->comment('消费额交易号');
            $table->integer('origin_id')->index()->default(0)->comment('用户ID');
            $table->tinyInteger('origin_type')->index()->default(1)->comment('用户类型 1-用户 2-商户 3-运营中心');
            $table->tinyInteger('type')->index()->default(1)->comment('来源类型 1-消费自返 2-直接下级消费返');
            $table->integer('order_id')->index()->default(0)->comment('分润的订单ID');
            $table->string('order_no')->index()->default('')->comment('分润的订单号');
            $table->decimal('pay_price', 8, 2)->default(0)->comment('支付金额');
            $table->decimal('order_profit_amount', 8, 2)->default(0)->comment('订单利润 (订单毛利润-税-分润金额)');
            $table->decimal('consume_quota', 8, 2)->default(0)->comment('消费额 [ =订单金额 ](总消费额满100返上级50)');
            $table->string('consume_user_mobile', 11)->default('')->comment('消费用户手机号');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-冻结中 2-已解冻待置换 3-已置换 4-已退款 5-置换失败');
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
        Schema::dropIfExists('wallet_consume_quota_records');
    }
}
