<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOrdersTableAddSplittingStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('splitting_status')->index()->default(1)->comment('是否已分润 1-未分润 2-已分润');
            $table->dateTime('splitting_time')->default(null)->nullable()->comment('分润时间');
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'splitting_status',
                'splitting_time',
            ]);
        });
    }
}
