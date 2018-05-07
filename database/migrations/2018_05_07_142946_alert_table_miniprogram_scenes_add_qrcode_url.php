<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMiniprogramScenesAddQrcodeUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('miniprogram_scenes', function (Blueprint $table) {
            $table->string('qrcode_url')->default('')->comment('二维码或小程序码url, 如果不存在则需要去微信生成');
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
        Schema::table('miniprogram_scenes', function (Blueprint $table) {
            $table->dropColumn([
                'qrcode_url',
            ]);
        });
    }
}
