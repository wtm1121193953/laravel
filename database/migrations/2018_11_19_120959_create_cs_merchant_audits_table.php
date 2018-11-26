<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsMerchantAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_merchant_audits', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->default(0)->comment('审核类型 1新增审核 2修改审核');
            $table->integer('cs_merchant_id')->default(0)->comment('审核超市商户ID');
            $table->integer('oper_id')->default(0)->comment('运营中心id')->after('id');
            $table->string('name')->default('')->comment('商户名称')->after('cs_merchant_id');
            $table->text('data_before')->comment('审核前数据json');
            $table->text('data_after')->comment('提交审核的数据json');
            $table->text('data_modify')->comment('修改的数据json');

            $table->tinyInteger('status')->default(0)->comment('审核状态 1待审核 2审核通过 3审核不通过');
            $table->string('suggestion')->default('')->comment('审核意见');
            $table->dateTime('audit_time')->nullable()->default(null)->comment('审核时间');
            $table->timestamps();
            $table->comment = '超市商户审核表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_merchant_audits');
    }
}
