<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 17:43
 */

namespace App\Http\Controllers\Cs;


use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Order\OrderService;
use App\Modules\Settlement\SettlementService;
use App\Result;
use Illuminate\Support\Facades\Storage;

class SettlementController extends Controller
{
    public function getList()
    {
        $data = SettlementService::getList([
            'merchantId' => request()->get('current_user')->merchant_id,
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

    public function getSettlementOrders()
    {
        $settlementId = request('settlement_id');
        $merchantId = request()->get('current_user')->merchant_id;
        $settlement = SettlementService::getByIdAndMerchantId($settlementId, $merchantId);
        if(empty($settlement)){
            throw new DataNotFoundException('结算单不存在');
        }

        $data = OrderService::getListByOperSettlementId($settlementId);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

}