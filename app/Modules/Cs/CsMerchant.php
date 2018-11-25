<?php

namespace App\Modules\Cs;

use App\BaseModel;
use App\Modules\Area\Area;
use App\Modules\Oper\Oper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CsMerchant
 * @package App\Modules\CsMerchant
 *
 * @property int    oper_id
 * @property int    merchant_category_id
 * @property string name
 * @property string brand
 * @property string signboard_name
 * @property int region
 * @property string province
 * @property int province_id
 * @property string city
 * @property int city_id
 * @property string area
 * @property int area_id
 * @property int business_time
 * @property string logo
 * @property string desc_pic
 * @property string desc_pic_list
 * @property string desc
 * @property string invoice_title
 * @property string invoice_no
 * @property int status
 * @property number lng
 * @property number lat
 * @property string address
 * @property string contacter
 * @property string contacter_phone
 * @property int settlement_cycle_type
 * @property number settlement_rate
 * @property string business_licence_pic_url
 * @property string organization_code
 * @property string tax_cert_pic_url
 * @property string legal_id_card_pic_a
 * @property string legal_id_card_pic_b
 * @property integer country_id
 * @property string corporation_name
 * @property string legal_id_card_num
 * @property string contract_pic_url
 * @property string hygienic_licence_pic_url
 * @property string agreement_pic_url
 * @property int bank_card_type
 * @property string bank_open_name
 * @property string bank_card_no
 * @property string bank_name
 * @property string sub_bank_name
 * @property string bank_province
 * @property integer bank_province_id
 * @property string bank_city
 * @property integer bank_city_id
 * @property string bank_area
 * @property integer bank_area_id
 * @property string bank_open_address
 * @property int audit_status
 * @property string audit_suggestion
 * @property string licence_pic_url
 * @property int audit_oper_id
 * @property int creator_oper_id
 * @property string service_phone
 * @property string bank_card_pic_a
 * @property string other_card_pic_urls
 * @property string oper_salesman
 * @property string oper_biz_member_code
 * @property Carbon active_time
 * @property Carbon first_active_time
 * @property number lowest_amount
 * @property int mapping_user_id
 * @property int level
 * @property int is_pilot
 * @property integer bizer_id
 * @property int user_follows
 *
 */
class CsMerchant extends BaseModel
{
    use SoftDeletes;

    /**
     * 需要转换成日期的属性
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    //
    /**
     * 未审核(审核中)
     */
    const AUDIT_STATUS_AUDITING = 0;
    /**
     * 审核通过
     */
    const AUDIT_STATUS_SUCCESS = 1;
    /**
     * 审核不通过
     */
    const AUDIT_STATUS_FAIL = 2;
    /**
     * 重新提交审核
     */
//    const AUDIT_STATUS_RESUBMIT = 3;
    /**
     * 审核不通过并且打回到商户池, 审核记录中才有该状态, 商家信息中直接置位审核不通过
     */
//    const AUDIT_STATUS_FAIL_TO_POOL = 4;
    /**
     * 取消审核
     */
//    const AUDIT_STATUS_CANCEL = 5;

    /**
     * 结算类型
     */
    const SETTLE_WEEKLY = 1; // 周结
    const SETTLE_HALF_MONTHLY = 2; // 半月结
    const SETTLE_DAILY_AUTO = 3; // T+1（自动）
    const SETTLE_HALF_YEARLY = 4; // 半年结
    const SETTLE_YEARLY = 5; // 年结
    const SETTLE_DAY_ADD_ONE = 6; // T+1（人工）

    /**
     * 试点商户
     */
    const PILOT_MERCHANT = 1;
    const NORMAL_MERCHANT = 0;

    /**
     * 商户状态
     */
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

    /**
     * 银行账户类型 1-公司账户 2-个人账户
     */
    const BANK_CARD_TYPE_COMPANY = 1;
    const BANK_CARD_TYPE_PEOPLE = 2;


    /**
     * 获取商户等级描述
     * @param $level
     * @return mixed
     */
    public static function getLevelText($level)
    {
        return ['', '签约商户', '联盟商户', '品牌商户'][$level];
    }

    public function oper()
    {
        return $this->belongsTo(Oper::class);
    }

    public function merchantFollow(){
        return $this->hasOne(MerchantFollow::class);
    }

    /**
     * 从请求中获取商户池数据, 并填充到当前实例中
     */
    public function fillMerchantPoolInfoFromRequest()
    {
        // 商户基本信息
        $this->name = request('name');
        $this->signboard_name = request('signboard_name', '');

        // 位置信息
        $provinceId = request('province_id', 0);
        $cityId = request('city_id', 0);
        $areaId = request('area_id', 0);
        $this->province = $provinceId ? Area::getNameByAreaId($provinceId) : '';
        $this->province_id = $provinceId;
        $this->city = $cityId ? Area::getNameByAreaId($cityId) : '';
        $this->city_id = $cityId;
        $this->area = $areaId ? Area::getNameByAreaId($areaId) : '';
        $this->area_id = $areaId;
        $this->lng = request('lng',0);
        $this->lat = request('lat',0);
        $this->address = request('address','');
    }

    /**
     * 获取商户状态
     * @param $auditStatus
     * @param $status
     * @return string
     */
    public static function getMerchantStatusText($auditStatus,$status){
        if(in_array($auditStatus,[1,3])){
            if($status == 1){
                $statusText = '正常';
            }elseif($status == 2){
                $statusText = '冻结';
            }else{
                $statusText = '';
            }
        }else{
            $statusText = '';
        }
        return $statusText;
    }

    /**
     * 从请求中获取商户激活需要的数据, 并填充到当前实例中
     */
    public function fillMerchantActiveInfoFromRequest()
    {
        $this->brand = request('brand','');
        $this->invoice_title = request('invoice_title','');
        $this->invoice_no = request('invoice_no','');
        $this->status = request('status', 1);
        $this->business_time = request('business_time', '');
        $this->logo = request('logo','');
        $descPicList = request('desc_pic_list', '');
        if(is_array($descPicList)) $descPicList = implode(',', $descPicList);
        $this->desc_pic_list = $descPicList;
        $this->desc = request('desc','');
        $this->settlement_cycle_type = request('settlement_cycle_type', 1);
        $this->settlement_rate = request('settlement_rate', 0.00);
        // 银行卡信息
        $this->bank_card_type = request('bank_card_type', 1);
        $this->bank_name = request('bank_name','');
        $this->bank_open_name = request('bank_open_name','');
        $this->bank_card_no = request('bank_card_no','');
        $bankProvinceId = request('bank_province_id', 0);
        $bankCityId = request('bank_city_id', 0);
        $bankAreaId = request('bank_area_id', 0);
        $this->bank_province = $bankProvinceId ? Area::getNameByAreaId($bankProvinceId) : '';
        $this->bank_province_id = $bankProvinceId;
        $this->bank_city = $bankCityId ? Area::getNameByAreaId($bankCityId) : '';
        $this->bank_city_id = $bankCityId;
        $this->bank_area = $bankAreaId ? Area::getNameByAreaId($bankAreaId) : '';
        $this->bank_area_id = $bankAreaId;
        $this->sub_bank_name = request('sub_bank_name','');
        $this->bank_open_address = request('bank_open_address','');
        $bankCardPicA = request('bank_card_pic_a','');
        if (is_array($bankCardPicA)) $bankCardPicA = implode(',', $bankCardPicA);
        $this->bank_card_pic_a = $bankCardPicA;
        $this->licence_pic_url = request('licence_pic_url','');

        $this->legal_id_card_pic_a = request('legal_id_card_pic_a','');
        $this->legal_id_card_pic_b = request('legal_id_card_pic_b','');
        $this->country_id = request('country_id', 0);
        $this->corporation_name = request('corporation_name', '');
        $this->legal_id_card_num = request('legal_id_card_num','');
        $this->business_licence_pic_url = request('business_licence_pic_url','');
        $this->organization_code = request('organization_code','');
        $contractPicUrl = request('contract_pic_url','');
        if (is_array($contractPicUrl)) $contractPicUrl = implode(',', $contractPicUrl);
        $this->contract_pic_url = $contractPicUrl;
        $otherCardPicUrls = request('other_card_pic_urls', '');
        if(is_array($otherCardPicUrls)) $otherCardPicUrls = implode(',', $otherCardPicUrls);
        $this->other_card_pic_urls = $otherCardPicUrls;
        // 商户负责人
        $this->contacter = request('contacter','');
        $this->contacter_phone = request('contacter_phone','');
        $this->service_phone = request('service_phone','');
        $this->oper_salesman = request('oper_salesman','');
        //$this->site_acreage = request('site_acreage','');
        //$this->employees_number = request('employees_number','');

        //试点商户
        //$this->is_pilot = request('is_pilot', 0);

        //新业务员ID
        //$this->bizer_id = request('bizer_id',0);


        //////// 没有了的字段
//        $this->region = request('region');
//        $this->tax_cert_pic_url = request('tax_cert_pic_url','');
//        $this->hygienic_licence_pic_url = request('hygienic_licence_pic_url','');
//        $this->agreement_pic_url = request('agreement_pic_url','');
    }

    /*protected $dispatchesEvents = [
        'created' => \App\Events\MerchantCreatedEvent::class,
    ];*/

}
