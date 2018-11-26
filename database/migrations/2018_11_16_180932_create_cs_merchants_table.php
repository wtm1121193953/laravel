<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_merchants', function (Blueprint $table) {
            $table->increments('id');
            // 商家基本信息
            $table->integer('oper_id')->index()->default(0)->comment('所属运营中心ID');
            $table->string('name')->default('')->comment('商家名称');
            $table->string('brand')->default('')->comment('商家品牌');
            $table->string('signboard_name')->default('')->comment('商家招牌名称');
            $table->tinyInteger('region')->index()->default(1)->comment('运营地区/大区 1-中国 2-美国 3-韩国 4-香港');
            $table->string('province')->default('')->comment('所在省份');
            $table->integer('province_id')->index()->default(0)->comment('所在省份Id');
            $table->string('city')->default('')->comment('所在城市');
            $table->integer('city_id')->index()->default(0)->comment('所在城市Id');
            $table->string('area')->default('')->comment('所在县区');
            $table->integer('area_id')->index()->default(0)->comment('所在县区Id');
            $table->string('business_time')->default('')->comment('营业时间, json格式字符串 {startTime, endTime}');
            $table->string('logo')->default('')->comment('商家logo');
            $table->string('desc_pic')->default('')->comment('商家介绍图片 [保留, 使用desc_pic_list]');
            $table->string('desc_pic_list', 2000)->default('')->comment('商家介绍图片列表  多图, 使用逗号分隔 ');
            $table->string('desc', 1000)->default('')->comment('商家介绍');
            $table->string('invoice_title')->default('')->comment('发票抬头 [废弃, 使用商户名或营业执照图片中的公司名]');
            $table->string('invoice_no')->default('')->comment('发票税号 [废弃, 同商户营业执照编号]');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常 2-禁用');
            $table->decimal('lng', 15, 12)->default(0)->comment('商家所在经度');
            $table->decimal('lat', 15, 12)->default(0)->comment('商家所在纬度');
            $table->string('address')->default('')->comment('商家地址');
            $table->string('contacter')->default('')->comment('负责人姓名');
            $table->string('contacter_phone')->default('')->comment('负责人联系方式');

            // 商务信息
            $table->tinyInteger('settlement_cycle_type')->default(1)->comment('结款周期 1-周结 2-半月结 3-T+1（自动） 4-半年结 5-年结 6-T+1（人工）');
            $table->decimal('settlement_rate', 4, 2)->default(0)->comment('分利比例(结算时的费率)');

            $table->string('business_licence_pic_url')->default('')->comment('营业执照');
            $table->string('organization_code')->default('')->comment('组织机构代码, 即营业执照代码');
            $table->string('tax_cert_pic_url')->default('')->comment('税务登记证');
            $table->string('legal_id_card_pic_a')->default('')->comment('法人身份证正面');
            $table->string('legal_id_card_pic_b')->default('')->comment('法人身份证反面');
            $table->integer('country_id')->default(1)->comment('国别或地区ID');

            $table->string('corporation_name')->default('')->comment('法人姓名');
            $table->string('legal_id_card_num')->default('')->comment('法人身份证号码');
            $table->string('contract_pic_url', 2000)->default('')->comment('合同照片, 多张图片使用逗号分隔');
            $table->string('licence_pic_url')->default('')->comment('开户许可证');
            $table->string('hygienic_licence_pic_url')->default('')->comment('卫生许可证');
            $table->string('agreement_pic_url')->default('')->comment('协议文件');

            // 银行信息
            $table->tinyInteger('bank_card_type')->default(1)->comment('银行账户类型 1-公司账户 2-个人账户');
            $table->string('bank_open_name')->default('')->comment('银行开户名');
            $table->string('bank_card_no')->default('')->comment('银行账号');
            $table->string('bank_name')->default('')->comment('开户行');
            $table->string('sub_bank_name')->default('')->comment('开户支行名称');
            $table->string('bank_province')->default('')->comment('开户行省份');
            $table->integer('bank_province_id')->default(0)->comment('开户行省份id');
            $table->string('bank_city')->default('')->comment('开户行城市');
            $table->integer('bank_city_id')->default(0)->comment('开户行城市id');
            $table->string('bank_area')->default('')->comment('开户行县区');
            $table->integer('bank_area_id')->default(0)->comment('开户行县区id');
            $table->string('bank_open_address')->default('')->comment('开户支行地址');

            $table->tinyInteger('audit_status')->default(0)->comment('商户资料审核状态 0-未审核 1-已审核 2-审核不通过 3-重新提交审核');
            $table->string('audit_suggestion',128)->default('')->comment('审核意见');

            $table->string('service_phone')->default('')->comment('客服电话');
            $table->string('bank_card_pic_a',500)->default('')->comment('法人银行卡正面照');
            $table->string('other_card_pic_urls',2000)->default('')->comment('其他证件照片');
            $table->string('oper_salesman')->default('')->comment('运营中心业务人员姓名');
            $table->string('oper_biz_member_code',10)->default('')->comment('运营中心业务员推荐码');

            $table->timestamp('active_time')->nullable()->comment('最近激活时间, 即商户最近一次审核通过时间');
            $table->timestamp('first_active_time')->nullable()->comment('首次审核通过时间');
            $table->integer('mapping_user_id')->dafault(0)->comment('商户关联的user_id');
            $table->tinyInteger('level')->default(1)->comment('商户等级 1-签约商户 2-联盟商户 3-品牌商户');
            $table->decimal('lowest_amount')->default(0.00)->comment('最低消费');
            $table->integer('user_follows')->dafault(0)->comment('用户关注总数');
            $table->timestamps();

            $table->softDeletes();
            $table->comment = '超市商家表';
        });

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cs_merchants`
	AUTO_INCREMENT=1000000000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_merchants');
    }
}
