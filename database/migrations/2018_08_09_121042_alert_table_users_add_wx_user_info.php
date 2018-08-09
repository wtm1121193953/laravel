<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableUsersAddWxUserInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->string('wx_nick_name')->default('')->comment('微信昵称')->after('status');
            $table->string('wx_avatar_url', 256)->default('')->comment('微信头像')->after('wx_nick_name');
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'wx_nick_name',
                'wx_avatar_url'
            ]);
        });
    }
}
