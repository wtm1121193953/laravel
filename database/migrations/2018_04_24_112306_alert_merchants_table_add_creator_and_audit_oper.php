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
            $table->string('service_phone')->default('')->comment('客服电话');
            $table->string('bank_card_pic_a')->default('')->comment('法人银行卡正面照');
            $table->string('other_card_pic_urls', 2000)->default('')->comment('其他证件照片');
            $table->string('oper_salesman')->default('')->comment('运营中心业务人员姓名');
            $table->string('site_acreage')->default('')->comment('商户面积');
            $table->string('employees_number')->default('')->comment('商户员工人数');
        });

        // 修改现有数据创建者运营中心ID与所属运营中心ID一直
        DB::table('merchants')->update(['creator_oper_id' => DB::raw('oper_id')]);
        // 修改现有数据, 商户审核者运营中心ID等于所属运营中心
        Merchant::where('audit_status', '>=', '0')->update(['audit_oper_id' => DB::raw('oper_id')]);
        // 修改现有数据, 未审核过的商家所属运营中心ID为0
        Merchant::where('audit_status', '=', '0')->update(['oper_id' => 0]);
        // 修改现有数据, 客服电话等同于负责人联系方式
        DB::table('merchants')->update(['service_phone' => DB::raw('contacter_phone')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Merchant::where('audit_status', '=', '0')->update(['oper_id' => DB::raw('creator_oper_id')]);

        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn([
                'creator_oper_id',
                'audit_oper_id',
                'service_phone',
                'bank_card_pic_a',
                'other_card_pic_urls',
                'oper_salesman',
                'site_acreage',
                'employees_number',
            ]);
        });
    }
}
