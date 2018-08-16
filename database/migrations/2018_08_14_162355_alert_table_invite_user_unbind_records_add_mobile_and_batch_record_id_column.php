<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableInviteUserUnbindRecordsAddMobileAndBatchRecordIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invite_user_unbind_records', function (Blueprint $table) {
            $table->string('mobile')->default('')->comment('解绑用户的手机号码')->after('status');
            $table->integer('batch_record_id')->index()->default(0)->comment('关联批量换绑记录表(invite_user_batch_changed_records)的ID')->after('mobile');
            $table->string('old_invite_user_record', 2000)->default('')->comment('原来的邀请记录')->after('batch_record_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invite_user_unbind_records', function (Blueprint $table) {
            $table->dropColumn([
                'mobile',
                'batch_record_id',
                'old_invite_user_record'
            ]);
        });
    }
}
