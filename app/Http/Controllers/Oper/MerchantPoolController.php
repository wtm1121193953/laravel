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
use App\Modules\Area\Area;
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
        $data = Merchant::where('audit_oper_id', 0)
            ->where('audit_status', 0)
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
        // 商户基本信息
        $merchant->merchant_category_id = request('merchant_category_id', 0);
        $merchant->name = request('name');
        $merchant->business_licence_pic_url = request('business_licence_pic_url','');
        $merchant->organization_code = request('organization_code','');

        // 位置信息
        $provinceId = request('province_id', 0);
        $cityId = request('city_id', 0);
        $areaId = request('area_id', 0);
        $merchant->province = $provinceId ? Area::getNameByAreaId($provinceId) : '';
        $merchant->province_id = $provinceId;
        $merchant->city = $cityId ? Area::getNameByAreaId($cityId) : '';
        $merchant->city_id = $cityId;
        $merchant->area = $areaId ? Area::getNameByAreaId($areaId) : '';
        $merchant->area_id = $areaId;
        $merchant->lng = request('lng',0);
        $merchant->lat = request('lat',0);
        $merchant->address = request('address','');
    }

    /**
     * 基础表单验证, 用于添加及编辑
     */
    protected function formValidate()
    {
        $this->validate(request(), [
            'name' => 'required',
            'merchant_category_id' => 'required|integer|min:1',
            'business_licence_pic_url' => 'required',
            'organization_code' => 'required',
            'lng' => 'required|numeric',
            'lat' => 'required|numeric',
            'province_id' => 'required|integer|min:1',
            'city_id' => 'required|integer|min:1',
            'area_id' => 'required|integer|min:1',
            'address' => 'required',
        ]);
    }

    /**
     * 添加商户池中的商户信息, 即商户的contract_status为2 并且该商户没有所属运营中心
     */
    public function add()
    {
        $this->formValidate();

        $merchant = new Merchant();
        $this->fillMerchantInfoFromRequest($merchant);

        // 商户营业执照代码不能重复
        $existMerchant = Merchant::where('organization_code', $merchant->organization_code)->first();
        if(!empty($existMerchant)) {
            throw new BaseResponseException('商户营业执照代码已存在');
        }

        $merchant->creator_oper_id = request()->get('current_user')->oper_id;
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
        $this->formValidate();

        $merchant = Merchant::findOrFail(request('id'));
        if($merchant->creator_oper_id != request()->get('current_user')->oper_id){
            throw new ParamInvalidException('不能修改其他运营中心录入的商户资料');
        }
        $this->fillMerchantInfoFromRequest($merchant);

        // 商户营业执照代码不能重复
        $existMerchant = Merchant::where('organization_code', $merchant->organization_code)->offset(1)->first();
        if(!empty($existMerchant)) {
            throw new BaseResponseException('商户营业执照代码已存在');
        }

        $merchant->save();
        return Result::success($merchant);
    }
}