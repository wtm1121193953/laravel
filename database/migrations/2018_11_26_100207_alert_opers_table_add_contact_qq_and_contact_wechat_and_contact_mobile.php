<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOpersTableAddContactQqAndContactWechatAndContactMobile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opers', function (Blueprint $table) {
            //
            $table->string('contact_qq')->default('')->comment('客服联系方式-QQ');
            $table->string('contact_wechat')->default('')->comment('客服联系方式-微信');
            $table->string('contact_mobile')->default('')->comment('客服联系方式-手机');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opers', function (Blueprint $table) {
            //
            $table->dropColumn([
                'contact_qq',
                'contact_wechat',
                'contact_mobile',
            ]);
        });
    }
}
