<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogReapalPayRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_reapal_pay_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->default(0)->comment('请求类型');
            $table->string('order_no',50)->index()->default('')->comment('订单号');
            $table->string('request', 5000)->default('')->comment('请求内容');
            $table->string('response',5000)->default('')->comment('相应内容');
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_reapal_pay_requests');
    }
}
