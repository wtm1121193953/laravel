<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMerchantsTableAddMappingUserIdColumn extends Migration
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
            $table->integer('mapping_user_id')->index()->default(0)->comment('商户关联的user_id');
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
                'mapping_user_id',
            ]);
        });
    }
}
