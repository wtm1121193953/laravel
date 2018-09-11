<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletConsumeQuotaUnfreezeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_consume_quota_unfreeze_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wallet_id')->index()->default(0)->comment('钱包ID');
            $table->integer('consume_quota_record_id')->default(0)->comment('消费额记录表id');
            $table->integer('origin_id')->index()->default(0)->comment('用户ID');
            $table->tinyInteger('origin_type')->index()->default(1)->comment('用户类型 1-用户 2-商户 3-运营中心');
            $table->decimal('unfreeze_consume_quota', 8, 2)->default(0)->comment('解冻消费额数量');
            $table->timestamps();

            $table->index('consume_quota_record_id', 'consume_quota_record_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_consume_quota_unfreeze_records');
    }
}
