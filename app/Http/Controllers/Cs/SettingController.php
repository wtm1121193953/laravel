<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/22/022
 * Time: 20:34
 */
namespace App\Http\Controllers\Cs;

use App\Exceptions\BaseResponseException;
use App\Modules\Cs\CsMerchantSettingService;
use App\Result;
use App\ResultCode;

class SettingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取配送设置
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeliverySetting()
    {
        $this->__init();

        $setting = CsMerchantSettingService::getDeliverSetting($this->_cs_merchant_id);

//        $setting->delivery_free_start = (string) $setting->delivery_free_start;
        return Result::success($setting);
    }


    /**
     * 保存配送设置
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDeliverySetting()
    {
        $this->__init();
        $setting = CsMerchantSettingService::getDeliverSetting($this->_cs_merchant_id);
        if (empty($setting)) {
            throw new BaseResponseException('系统错误');
        }
        $deliveryStartPrice = request('delivery_start_price');
        $deliveryCharges = request('delivery_charges');
        $deliveryFreeStart = request('delivery_free_start');
        $deliveryFreeOrderAmount = request('delivery_free_order_amount');

        if ($deliveryCharges != 0 && $deliveryFreeStart == 1 && $deliveryFreeOrderAmount < $deliveryStartPrice) {
            throw new BaseResponseException('订单满免配送费的价格不能小于起送价');
        }

        $setting->delivery_start_price = $deliveryStartPrice;
        $setting->delivery_charges = $deliveryCharges;
        $setting->delivery_free_start = $deliveryFreeStart;
        $setting->delivery_free_order_amount = $deliveryFreeOrderAmount;

        $rs = $setting->save();
        if ($rs) {
            return Result::success('保存成功');
        } else {
            return Result::error(ResultCode::DB_INSERT_FAIL,'保存失败');
        }
    }

}