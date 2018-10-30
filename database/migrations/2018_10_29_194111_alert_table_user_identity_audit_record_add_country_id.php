<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableUserIdentityAuditRecordAddCountryId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_identity_audit_records', function (Blueprint $table) {
            $table->integer('country_id')->default(1)->comment('国别或地区ID')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_identity_audit_records', function (Blueprint $table) {
            $table->dropColumn([
                'country_id'
            ]);
        });
    }
}
