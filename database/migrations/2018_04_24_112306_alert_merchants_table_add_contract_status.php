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
            $table->integer('auditing_oper_id')->default(0)->comment('当前提交审核的运营中心ID, 为0时才会出现在商户池中');
            $table->integer('creator_oper_id')->default(0)->comment('录入资料的运营中心ID');
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
            $table->dropColumn(['creator_oper_id', 'auditing_oper_id']);
        });
    }
}
