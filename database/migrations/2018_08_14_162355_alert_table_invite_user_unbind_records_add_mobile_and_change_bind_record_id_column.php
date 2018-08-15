<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableInviteUserUnbindRecordsAddMobileAndChangeBindRecordIdColumn extends Migration
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
            $table->integer('change_bind_record_id')->index()->default(0)->comment('关联换绑记录表的ID')->after('mobile');
            $table->string('old_invite_user_record', 2000)->default('')->comment('原来的邀请记录')->after('change_bind_record_id');
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
                'change_bind_record_id',
                'old_invite_user_record'
            ]);
        });
    }
}
