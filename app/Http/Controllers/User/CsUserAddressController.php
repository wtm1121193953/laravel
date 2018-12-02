<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 10:47 AM
 */

namespace App\HTTP\Controllers\User;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantSettingService;
use App\Modules\Cs\CsUserAddressService;
use App\Modules\Setting\SettingService;
use App\Result;

class CsUserAddressController extends Controller{

    /**
     * 添加收货地址
     * @author zwg
     * @date  181120
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUserAddresses(){
        $this->validate(request(), [
            'contact_phone' => 'required|regex:/^1[3,4,5,6,7,8,9]\d{9}$/',
            'contacts' => 'required|min:1|max:30|regex:/^[\x7f-\xff]+$/',
        ]);
        $default = 0;
        if (!empty(request('is_default'))){
            $default = request('is_default');
        }
        $data = ['contacts' => request('contacts'),
            'contact_phone' => request('contact_phone'),
            'province_id' => request('province_id'),
            'city_id' => request('city_id'),
            'area_id' => request('area_id'),
            'address' => request('address'),
            'is_default' => $default];
        CsUserAddressService::addAddresses($data);
        return Result::success('添加收货地址成功');
    }

    /**
     * 获取地址列表
     */
    public function getAddresses(){
        $isTestAddress = 0; //是否检测配送范围
        if (!empty(request('is_test_radius'))){
            $isTestAddress = request('is_test_radius');
        }
        $cityId = request('city_id');
        $city_wide = SettingService::getValueByKey('city_limit');
        $query = CsUserAddressService::getList($isTestAddress,$cityId,$city_wide);
        return Result::success($query);
    }

    /**
     * 编辑收货地址
     */
    public function editAddress(){
        $this->validate(request(), [
            'id' => 'required',
            'contact_phone' => 'required|regex:/^1[3,4,5,6,7,8,9]\d{9}$/',
            'contacts' => 'required|min:1|max:30|regex:/^[\x7f-\xff]+$/',
        ]);
        $data = Array();
        $data['id'] = request('id');
        if (!empty(request('contacts'))){
            $data['contacts'] = request('contacts');
        }
        if (!empty(request('contact_phone'))){
            $data['contact_phone'] = request('contact_phone');
        }
        if (!empty(request('province_id'))){
            $data['province_id'] = request('province_id');
        }
        if (!empty(request('city_id'))){
            $data['city_id'] = request('city_id');
        }
        if (!empty(request('area_id'))){
            $data['area_id'] = request('area_id');
        }
        if (!empty(request('address'))){
            $data['address'] = request('address');
        }
        if (!empty(request('is_default'))){
            $data['is_default'] = request('is_default');
        }

        CsUserAddressService::editAddress($data);
        return Result::success('更新收货地址成功');
    }

    /**
     * 删除收货地址
     * @throws \Exception
     */
    public function deleteAddress(){
        $this->validate(request(), [
            'id' => 'required'
        ]);
        CsUserAddressService::delAddress(request('id'));
        return Result::success('删除收货地址成功');
    }

    /**
     * 获取配送设置 是否限制只能选择同城地址 和 是否只支持同城配送
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeliverySetting()
    {
        $cityLimit = SettingService::getValueByKey('supermarket_city_limit');
        $showCityLimit = SettingService::getValueByKey('supermarket_supermarket_city_limit');

        return Result::success([
            'city_limit' => $cityLimit,
            'show_city_limit' => $showCityLimit,
        ]);
    }

    /**
     * 获取商家配送费设置
     * @return \Illuminate\Http\JsonResponse
     */
    public function merchantSetting()
    {
        $this->validate(request(), [
            'merchantId' => 'required|integer|min:1',
        ]);
        $merchantId = request('merchantId');

        $merchantSetting = CsMerchantSettingService::getDeliverSetting($merchantId);
        if ($merchantSetting->delivery_free_order_amount <= 0) {
            throw new BaseResponseException('配送费设置有误');
        }

        return Result::success($merchantSetting);
    }
}