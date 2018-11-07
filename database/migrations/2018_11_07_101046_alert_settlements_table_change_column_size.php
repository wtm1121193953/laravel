<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertSettlementsTableChangeColumnSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->string('pay_pic_url', 255)->default('')->comment('回款单图片')->change();
            $table->string('invoice_pic_url', 255)->default('')->comment('发票图片地址  电子发票有效')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->dropColumn([
                'pay_pic_url',
                'invoice_pic_url',
            ]);
        });
    }
}
