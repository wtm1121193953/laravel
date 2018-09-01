<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeeSplittingRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_splitting_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('origin_id')->index()->comment('分润用户ID, 用户ID/商户ID/或运营中心ID');
            $table->tinyInteger('origin_type')->index()->default(1)->comment('分润用户类型, 1-用户 2-商户 3-运营中心');
            $table->tinyInteger('merchant_level')->default(0)->comment('商户等级, 分润用户类型为商户时有效');
            $table->integer('order_id')->index()->default(0)->comment('分润的订单ID');
            $table->string('order_no')->index()->default('')->comment('分润的订单号');
            $table->decimal('order_profit_amount', 8, 2)->default(0)->comment('订单利润');
            $table->decimal('ratio', 5, 2)->default(0)->comment('返利时的分润比例');
            $table->decimal('amount', 8, 2)->default(0)->comment('分润金额, 单位: 元');
            $table->tinyInteger('type')->index()->default(1)->comment('分润类型 1-自返, 2-返上级 3-运营中心交易分润');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-冻结中 2-已解冻 3-已退款退回');
            $table->timestamps();

            $table->comment = '分润记录表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_splitting_records');
    }
}
