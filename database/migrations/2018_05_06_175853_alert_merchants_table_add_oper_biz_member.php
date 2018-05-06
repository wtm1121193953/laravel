<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMerchantsTableAddOperBizMember extends Migration
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
            $table->string('oper_biz_member_code', 10)->index()->default('')->comment('运营中心业务员推荐码');
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
            $table->dropColumn([
                'oper_biz_member_code',
            ]);
        });
    }
}
