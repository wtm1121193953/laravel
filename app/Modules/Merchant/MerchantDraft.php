<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use App\Modules\Area\Area;

class MerchantDraft extends BaseModel
{
    //
    /**
     * 从请求中获取商户池数据, 并填充到当前实例中
     */
    public function fillMerchantPoolInfoFromRequest()
    {
        // 商户基本信息
        $this->merchant_category_id = request('merchant_category_id', 0);
        $this->name = request('name');

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
     * 从请求中获取商户激活需要的数据, 并填充到当前实例中
     */
    public function fillMerchantActiveInfoFromRequest()
    {
        $this->oper_biz_member_code = request('oper_biz_member_code','');
        $this->brand = request('brand','');
        $this->signboard_name = request('signboard_name', '');
        $this->invoice_title = request('invoice_title','');
        $this->invoice_no = request('invoice_no','');
        $this->status = request('status', 1);
        $this->business_time = request('business_time', '');
        $this->logo = request('logo','');
        $descPicList = request('desc_pic_list', '');
        if(is_array($descPicList)) $descPicList = implode(',', $descPicList);
        $this->desc_pic_list = $descPicList;
        $this->desc = request('desc','');
        $this->settlement_cycle_type = request('settlement_cycle_type');
        $this->settlement_rate = request('settlement_rate');
        // 银行卡信息
        $this->bank_card_type = request('bank_card_type');
        $this->bank_open_name = request('bank_open_name','');
        $this->bank_card_no = request('bank_card_no','');
        $this->sub_bank_name = request('sub_bank_name','');
        $this->bank_open_address = request('bank_open_address','');
        $this->bank_card_pic_a = request('bank_card_pic_a','');
        $this->licence_pic_url = request('licence_pic_url','');

        $this->legal_id_card_pic_a = request('legal_id_card_pic_a','');
        $this->legal_id_card_pic_b = request('legal_id_card_pic_b','');
        $this->business_licence_pic_url = request('business_licence_pic_url','');
        $this->organization_code = request('organization_code','');
        $this->contract_pic_url = request('contract_pic_url','');
        $otherCardPicUrls = request('other_card_pic_urls', '');
        if(is_array($otherCardPicUrls)) $otherCardPicUrls = implode(',', $descPicList);
        $this->other_card_pic_urls = $otherCardPicUrls;
        // 商户负责人
        $this->contacter = request('contacter','');
        $this->contacter_phone = request('contacter_phone','');
        $this->service_phone = request('service_phone','');
        $this->oper_salesman = request('oper_salesman','');
        $this->site_acreage = request('site_acreage','');
        $this->employees_number = request('employees_number','');


        //////// 没有了的字段
//        $this->region = request('region');
//        $this->tax_cert_pic_url = request('tax_cert_pic_url','');
//        $this->hygienic_licence_pic_url = request('hygienic_licence_pic_url','');
//        $this->agreement_pic_url = request('agreement_pic_url','');
    }
}
