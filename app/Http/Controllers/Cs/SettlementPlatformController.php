<?php

namespace App\Http\Controllers\Cs;

//use App\Modules\Order\OrderService;
use App\Http\Controllers\Controller;

use App\Exceptions\DataNotFoundException;
use App\Modules\Settlement\SettlementPlatform;
use App\Result;
use App\Modules\Settlement\SettlementPlatformService;

/**
 * Class SettlementPlatformController
 * @package App\Http\Controllers\Merchant
 * Author:  Jerry
 * Date: 180825
 */
class SettlementPlatformController extends Controller
{
    public function getList()
    {
        $data = SettlementPlatformService::getList([
            'merchantId' => request()->get('current_user')->merchant_id,
            'merchant_type' =>  SettlementPlatform::MERCHANT_TYPE_CS,
        ]);

        $data->each(function ($item) {
            $item->invoice_pic_url_arr = $item->invoice_pic_url ? explode(',', $item->invoice_pic_url) : '';
            $item->pay_pic_url_arr = $item->pay_pic_url ? explode(',', $item->pay_pic_url) : '';
        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取结算单的订单列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettlementOrders()
    {
        $settlementId = request('settlement_id');
        $merchantId = request()->get('current_user')->merchant_id;
        $settlement = SettlementPlatformService::getByIdAndMerchantId($settlementId, $merchantId);
        if(empty($settlement)){
            throw new DataNotFoundException('结算单不存在');
        }
        $data = SettlementPlatformService::getSettlementOrders($settlementId);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

}
