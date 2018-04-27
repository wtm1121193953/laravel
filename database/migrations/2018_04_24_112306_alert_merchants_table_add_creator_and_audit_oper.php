<?php

use App\Modules\Merchant\Merchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMerchantsTableAddCreatorAndAuditOper extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchants', function (Blueprint $table) {
            $table->integer('audit_oper_id')->index()->default(0)->comment('当前提交审核的运营中心ID, 为0时才会出现在商户池中');
            $table->integer('creator_oper_id')->index()->default(0)->comment('录入资料的运营中心ID');
        });

        // 修改现有数据创建者运营中心ID与所属运营中心ID一直
        DB::table('merchants')->update(['creator_oper_id' => DB::raw('oper_id')]);
        // 修改现有数据, 已审核过的商家审核者运营中心ID等于所属运营中心
        Merchant::where('audit_status', '>', '0')->update(['audit_oper_id' => DB::raw('oper_id')]);
        // 修改现有数据, 未审核过的商家所属运营中心ID为0
        Merchant::where('audit_status', '=', '0')->update(['oper_id' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['creator_oper_id', 'audit_oper_id']);
        });
        Merchant::where('audit_status', '=', '0')->update(['oper_id' => DB::raw('creator_oper_id')]);
    }
}
