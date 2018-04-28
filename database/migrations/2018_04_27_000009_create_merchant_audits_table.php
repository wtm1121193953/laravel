<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_audits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->index()->comment('商户ID');
            $table->integer('oper_id')->index()->comment('提交审核的运营中心ID');
            $table->tinyInteger('status')->index()->default(0)->comment('状态 0-待审核 1-审核通过 2-审核不通过 3-重新提交审核 4-已被打回到商户池');
            $table->timestamps();

            $table->comment = '商家审核记录表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_audits');
    }
}
