<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuditSuggestionToMerchantAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_audits', function (Blueprint $table) {
            $table->string('audit_suggestion', 128)->default('')
                ->comment('审核意见')
                ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchant_audits', function (Blueprint $table) {
            $table->dropColumn([
                'audit_suggestion',
            ]);
        });
    }
}
