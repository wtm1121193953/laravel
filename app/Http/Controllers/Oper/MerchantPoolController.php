<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/4/24
 * Time: 14:34
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategory;
use App\Result;

/**
 * 商户池控制器, 用户获取未签订合同的商户
 * Class MerchantPoolController
 * @package App\Http\Controllers\Oper
 */
class MerchantPoolController extends Controller
{

    public function getList()
    {
        $data = Merchant::where('contract_status', Merchant::CONTRACT_STATUS_NO)
            ->orderBy('id', 'desc')
            ->paginate();

        $data->each(function ($item){
            if ($item->merchant_category_id){
                $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
            }
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $merchant = Merchant::findOrFail($id);
        if ($merchant->merchant_category_id){
            $merchant->categoryPath = MerchantCategory::getCategoryPath($merchant->merchant_category_id);
        }
        $merchant->desc_pic_list = $merchant->desc_pic_list ? explode(',', $merchant->desc_pic_list) : [];
        return Result::success($merchant);
    }

    private function fillMerchantInfoFromRequest(Merchant $merchant)
    {
        // todo 整理添加商户池中商户信息需要的字段
        $merchant->merchant_category_id = request('merchant_category_id', 0);
        $merchant->name = request('name');
        $merchant->brand = request('brand','');
        $merchant->region = request('region');
        $merchant->province = request('province_id') ? Area::where('area_id', request('province_id'))->value('name') : '';
        $merchant->province_id = request('province_id', 0);
        $merchant->city = request('city_id') ? Area::where('area_id', request('city_id'))->value('name') : '';
        $merchant->city_id = request('city_id', 0);
        $merchant->area = request('area_id') ? Area::where('area_id', request('area_id'))->value('name') : '';
        $merchant->area_id = request('area_id', 0);
        $merchant->business_time = request('business_time');
        $merchant->logo = request('logo','');
        $merchant->desc_pic = request('desc_pic','');
        $descPicList = request('desc_pic_list', '');
        if(is_array($descPicList)){
            $descPicList = implode(',', $descPicList);
        }
        $merchant->desc_pic_list = $descPicList;
        $merchant->desc = request('desc','');
        $merchant->invoice_title = request('invoice_title','');
        $merchant->invoice_no = request('invoice_no','');
        $merchant->status = request('status', 1);
        $merchant->lng = request('lng',0);
        $merchant->lat = request('lat',0);
        $merchant->address = request('address','');
        $merchant->contacter = request('contacter','');
        $merchant->contacter_phone = request('contacter_phone','');
        $merchant->settlement_cycle_type = request('settlement_cycle_type');
        $merchant->settlement_rate = request('settlement_rate');
        $merchant->business_licence_pic_url = request('business_licence_pic_url','');
        $merchant->organization_code = request('organization_code','');
        $merchant->tax_cert_pic_url = request('tax_cert_pic_url','');
        $merchant->legal_id_card_pic_a = request('legal_id_card_pic_a','');
        $merchant->legal_id_card_pic_b = request('legal_id_card_pic_b','');
        $merchant->contract_pic_url = request('contract_pic_url','');
        $merchant->licence_pic_url = request('licence_pic_url','');
        $merchant->hygienic_licence_pic_url = request('hygienic_licence_pic_url','');
        $merchant->agreement_pic_url = request('agreement_pic_url','');
        $merchant->bank_card_type = request('bank_card_type');
        $merchant->bank_open_name = request('bank_open_name','');
        $merchant->bank_card_no = request('bank_card_no','');
        $merchant->sub_bank_name = request('sub_bank_name','');
        $merchant->bank_open_address = request('bank_open_address','');
    }

    /**
     * 添加商户池中的商户信息, 即商户的contract_status为2 并且该商户没有所属运营中心
     */
    public function add()
    {
        $merchant = new Merchant();
        $this->fillMerchantInfoFromRequest($merchant);

        // 商户组织机构代码不能重复
        $existMerchant = Merchant::where('organization_code', $merchant->organization_code)->first();
        if(!empty($existMerchant)) {
            throw new BaseResponseException('商户组织机构代码已存在');
        }

        $merchant->creator_oper_id = request()->get('current_user')->oper_id;
        $merchant->contract_status = Merchant::CONTRACT_STATUS_NO;
        $merchant->save();
        return Result::success($merchant);
    }

    /**
     * 修改商户池中的商户信息
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');
        $merchant = Merchant::findOrFail($id);
        if($merchant->creator_oper_id != request()->get('current_user')->oper_id){
            throw new ParamInvalidException('不能修改其他运营中心录入的商户资料');
        }

        // 商户组织机构代码不能重复
        $existMerchant = Merchant::where('organization_code', $merchant->organization_code)->offset(1)->first();
        if(!empty($existMerchant)) {
            throw new BaseResponseException('商户组织机构代码已存在');
        }

        $this->fillMerchantInfoFromRequest($merchant);
        $merchant->save();
        return Result::success($merchant);
    }
}