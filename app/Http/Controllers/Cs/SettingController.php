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
        $setting->delivery_start_price = request('delivery_start_price');
        $setting->delivery_charges = request('delivery_charges');
        $setting->delivery_free_start = request('delivery_free_start');
        $setting->delivery_free_order_amount = request('delivery_free_order_amount');

        $rs = $setting->save();
        if ($rs) {
            return Result::success('保存成功');
        } else {

            return Result::error('保存失败');
        }
    }

}