<?php

namespace App\Http\Controllers\Merchant;

use App\Exports\MerchantOrderExport;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesItem;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\OrderService;
use App\Modules\Payment\Payment;
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

        $list = $query->orderByDesc('id')
            ->select('order_no', 'user_id', 'user_name', 'notify_mobile', 'merchant_id', 'type', 'goods_id', 'goods_name', 'dishes_id', 'price', 'buy_number', 'status', 'pay_type', 'pay_price', 'pay_time', 'pay_target_type', 'refund_price', 'refund_time', 'finish_time', 'created_at', 'origin_app_type','remark')
            ->get();
        //$merchantIds = $list->pluck('merchant_id');
        //$merchants = Merchant::whereIn('id', $merchantIds->all())->get(['id', 'name'])->keyBy('id');
        //$payments = Payment::getAllType();
        $list->each(function($item){
            //$item->merchant_name = isset($merchants[$item->merchant_id]) ? $merchants[$item->merchant_id]->name : '';
            //$item->pay_type_name = $payments[$item->pay_type]??'';
            if ($item->type == 3){
                $dishesItems = DishesItem::where('dishes_id', $item->dishes_id)->get();
                $item->dishes_items = $dishesItems;
            }
        });

        return (new MerchantOrderExport($list))->download('商户中心订单管理列表.xlsx');
    }

    public function verification()
    {
        $verify_code = request('verify_code');
        $merchantId = request()->get('current_user')->merchant_id;

        $order = OrderService::verifyOrder($merchantId, $verify_code);

        return Result::success($order);
    }

}