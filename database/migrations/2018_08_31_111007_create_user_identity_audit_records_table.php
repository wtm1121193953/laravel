<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserIdentityAuditRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_identity_audit_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->string('name')->default('')->comment('姓名');
            $table->string('id_card_no')->index()->default('')->comment('身份证号码');
            $table->string('front_pic')->comment('身份证正面照');
            $table->string('opposite_pic')->comment('身份证反面照');
            $table->tinyInteger('status')->default(1)->comment('状态 1：为待审核，2：为审核通过， 3：为审核失败');
            $table->string('reason')->default('')->comment('不通过原因');
            $table->integer('update_user')->default(0)->comment('审核工作人员ID');
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
        Schema::dropIfExists('user_identity_audit_records');
    }
}
