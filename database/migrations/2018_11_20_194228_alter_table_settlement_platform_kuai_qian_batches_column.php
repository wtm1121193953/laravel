<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSettlementPlatformKuaiQianBatchesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlement_platform_kuai_qian_batches', function (Blueprint $table) {
            //
            $table->tinyInteger('merchant_type')->default(1)->comment('商户类型 1-普通商户 2-超市商户')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settlement_platform_kuai_qian_batches', function (Blueprint $table) {
            //
            $table->dropColumn([
                'merchant_type'
            ]);
        });
    }
}
