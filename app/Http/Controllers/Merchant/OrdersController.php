<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 13:18
 */

namespace App\Http\Controllers\Merchant;


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
        $data = OrderService::getList([
            'merchantId' => $merchantId,
            'orderNo' => $orderNo,
            'notifyMobile' => $notifyMobile,
            'keyword' => $keyword,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function verification()
    {
        $verify_code = request('verify_code');
        $merchantId = request()->get('current_user')->merchant_id;

        $order = OrderService::verifyOrder($merchantId, $verify_code);

        return Result::success($order);
    }

}