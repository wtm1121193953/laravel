<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMerchantsTableAddContractStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchants', function (Blueprint $table) {
            $table->integer('creator_oper_id')->default(0)->comment('录入资料的运营中心ID');
            $table->tinyInteger('contract_status')->default(1)->comment('合同签订状态 1-已签订 2-未签订 (未签订合同的商家只有资料, 没有后续操作, 且没有所属运营中心)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['entry_oper_id', 'contract_status']);
        });
    }
}
