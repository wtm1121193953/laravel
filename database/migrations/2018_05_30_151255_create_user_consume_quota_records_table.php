<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserConsumeQuotaRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_consume_quota_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->decimal('consume_quota')->default(0)->comment('产生消费额');
            $table->tinyInteger('type')->index()->default(1)->comment('来源类型 (1-消费自返 2-直接下级消费返 3-退款退回)');
            $table->string('order_no')->index()->default('')->comment('关联订单号');
            $table->string('consume_user_mobile', 11)->default('')->comment('消费用户手机号');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_consume_quota_records');
    }
}
