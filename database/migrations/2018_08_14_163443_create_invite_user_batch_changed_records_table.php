<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteUserBatchChangedRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_user_batch_changed_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invite_channel_name')->index()->default('')->comment('渠道名称');
            $table->string('remark')->default('')->comment('备注');
            $table->integer('oper_id')->default(0)->comment('运营中心ID');
            $table->string('oper_name')->index()->default('')->comment('运营中心名称');
            $table->integer('change_bind_number')->default(0)->comment('此次换绑记录的换绑人数');
            $table->string('bind_mobile')->default('')->comment('新绑定的手机号');
            $table->integer('operator_id')->default(0)->comment('操作人ID');
            $table->string('operator')->default('')->comment('操作人');
            $table->timestamps();

            $table->comment = '批量换绑记录表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invite_user_change_bind_records');
    }
}
