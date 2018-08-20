<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 13:18
 */

namespace App\Http\Controllers\Merchant;


use App\Exports\MerchantOrderExport;
use App\Http\Controllers\Controller;
use App\Modules\Order\OrderService;
use App\Result;

class OrdersController extends Controller
{
    public function getList()
    {
        $keyword = request('keyword');
        $orderNo = request('orderNo');
        $notifyMobile = request('notifyMobile');
        $merchantId = request()->get('current_user')->merchant_id;
        $createdAt = request('createdAt');
        $type = request('type');
        $status = request('status');
        $goodsName = request('goodsName');
        if(count($createdAt) > 1){
            $startCreatedAt = $createdAt[0] . ' 00:00:00';
            $endCreatedAt = $createdAt[1] . ' 23:59:59';
        }else {
            $startCreatedAt = $endCreatedAt = null;
        }
        $data = OrderService::getList([
            'merchantId' => $merchantId,
            'orderNo' => $orderNo,
            'notifyMobile' => $notifyMobile,
            'keyword' => $keyword,
            'type' => $type,
            'status' => $status,
            'goodsName' => $goodsName,
            'getWithQuery' => false,
            'startCreatedAt' => $startCreatedAt,
            'endCreatedAt' => $endCreatedAt,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function export()
    {

        $keyword = request('keyword');
        $orderNo = request('orderNo');
        $notifyMobile = request('notifyMobile');
        $merchantId = request()->get('current_user')->merchant_id;
        $createdAt = explode(',',request('createdAt', ''));
        $type = request('type');
        $status = request('status');
        $goodsName = request('goodsName');
        if(count($createdAt) > 1){
            $startCreatedAt = $createdAt[0] . ' 00:00:00';
            $endCreatedAt = $createdAt[1] . ' 23:59:59';
        }else {
            $startCreatedAt = $endCreatedAt = null;
        }

        $query = OrderService::getList([
            'merchantId' => $merchantId,
            'orderNo' => $orderNo,
            'notifyMobile' => $notifyMobile,
            'keyword' => $keyword,
            'createdAt' => $createdAt,
            'type' => $type,
            'status' => $status,
            'goodsName' => $goodsName,
            'startCreatedAt' => $startCreatedAt,
            'endCreatedAt' => $endCreatedAt,
        ], true);

        return (new MerchantOrderExport($query))->download('商户中心订单管理列表.xlsx');
    }

    public function verification()
    {
        $verify_code = request('verify_code');
        $merchantId = request()->get('current_user')->merchant_id;

        $order = OrderService::verifyOrder($merchantId, $verify_code);

        return Result::success($order);
    }

}