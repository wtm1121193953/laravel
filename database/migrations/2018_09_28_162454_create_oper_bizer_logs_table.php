<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperBizerLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oper_bizer_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('运营中心ID');
            $table->integer('bizer_id')->index()->default(0)->comment('业务员ID');
            $table->string('note')->default('')->comment('签约或拒绝原因');
            $table->string('status')->index()->default(0)->comment('状态 0申请中, 1-签约 -1-拒绝');
            $table->dateTime('apply_time')->comment('申请时间');
            $table->string('remark')->default('')->comment('申请备注');
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
        Schema::dropIfExists('oper_bizer_logs');
    }
}
