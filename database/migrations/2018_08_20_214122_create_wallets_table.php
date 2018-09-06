<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('origin_id')->index()->default(0)->comment('用户ID');
            $table->tinyInteger('origin_type')->index()->default(1)->comment('用户类型 1-用户 2-商户 3-运营中心');
            $table->decimal('balance', 11, 2)->default(0)->comment('钱包总金额(不包含冻结金额)');
            $table->decimal('freeze_balance', 11, 2)->default(0)->comment('冻结金额');
            $table->decimal('consume_quota', 11, 2)->default(0)->comment('当月消费额(不包含冻结消费额)');
            $table->decimal('freeze_consume_quota', 11, 2)->default(0)->comment('当月冻结中的消费额');
            $table->decimal('share_consume_quota', 11, 2)->default(0)->comment('下级贡献的消费额（不包含冻结的）');
            $table->decimal('share_freeze_consume_quota', 11, 2)->default(0)->comment('下级贡献冻结中的消费额');
            $table->string('withdraw_password')->default('')->comment('提现密码');
            $table->string('salt')->default('')->comment('盐值');
            $table->tinyInteger('status')->default(1)->comment('状态 1-正常 2-冻结');
            $table->timestamps();

            $table->comment = '钱包表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallets');
    }
}
