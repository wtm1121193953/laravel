<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableUserConsumeQuotaRecordsAddInoutTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('user_consume_quota_records', function (Blueprint $table) {
            $table->tinyInteger('inout_type')->index()->default(1)->comment('收支类型 1-收入 2-支出')->after('consume_quota');
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
        Schema::table('user_consume_quota_records', function (Blueprint $table) {
            $table->dropColumn([
                'inout_type',
            ]);
        });
    }
}
