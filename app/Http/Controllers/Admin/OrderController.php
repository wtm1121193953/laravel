<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/17/017
 * Time: 15:34
 */
namespace App\Http\Controllers\Admin;

use App\Exports\OperOrderExport;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Modules\Order\OrderService;
use App\Result;

class OrderController extends Controller
{
    public function getList()
    {
        $orderNo = request('orderNo');
        $mobile = request('mobile');
        $merchantId = request('merchantId');
        $timeType = request('timeType', 'payTime');
        $startTime = request('startTime');
        $endTime = request('endTime');
        $status = request('status');
        $type = request('type');

        if($timeType == 'payTime'){
            $startPayTime = $startTime;
            $endPayTime = $endTime;
        }else {
            $startFinishTime = $startTime;
            $endFinishTime = $endTime;
        }

        $data = OrderService::getList([
            'merchantId' => $merchantId,
            'orderNo' => $orderNo,
            'notifyMobile' => $mobile,
            'startPayTime' => $startPayTime ?? null,
            'endPayTime' => $endPayTime ?? null,
            'startFinishTime' => $startFinishTime ?? null,
            'endFinishTime' => $endFinishTime ?? null,
            'status' => $status,
            'type' => $type,
            'platform_only' => request('platform_only')=='true',
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function getOptions()
    {
        $opers = OperService::allOpers();
        $merchants = MerchantService::getAllNames([]);
        $list['opers'] = $opers;
        $list['merchants'] = $merchants->toArray();
        return Result::success([
            'list' => $list
        ]);
    }

    /**
     * 导出订单
     */
    public function export()
    {
        $orderNo = request('orderNo');
        $mobile = request('mobile');
        $merchantId = request('merchantId');
        $timeType = request('timeType', 'payTime');
        $startTime = request('startTime');
        $endTime = request('endTime');
        $type = request('type');
        $status = request('status');

        if($timeType == 'payTime'){
            $startPayTime = $startTime;
            $endPayTime = $endTime;
        }else {
            $startFinishTime = $startTime;
            $endFinishTime = $endTime;
        }

        $query = OrderService::getList([
            'operId' => request()->get('current_user')->oper_id,
            'merchantId' => $merchantId,
            'orderNo' => $orderNo,
            'notifyMobile' => $mobile,
            'startPayTime' => $startPayTime ?? null,
            'endPayTime' => $endPayTime ?? null,
            'startFinishTime' => $startFinishTime ?? null,
            'endFinishTime' => $endFinishTime ?? null,
            'status' => $status,
            'type' => $type,
            'platform_only' => request('platform_only')=='true',
        ], true);

        $list = $query->orderByDesc('id')
            ->select('order_no', 'user_id', 'user_name', 'notify_mobile', 'merchant_id', 'type', 'goods_id', 'goods_name', 'price', 'buy_number', 'status', 'pay_type', 'pay_price', 'pay_time', 'pay_target_type', 'refund_price', 'refund_time', 'finish_time', 'created_at', 'origin_app_type')
            ->get();
        $merchantIds = $list->pluck('merchant_id');
        $merchants = Merchant::whereIn('id', $merchantIds->all())->get(['id', 'name'])->keyBy('id');
        $list->each(function($item) use ($merchants){
            $item->merchant_name = isset($merchants[$item->merchant_id]) ? $merchants[$item->merchant_id]->name : '';
        });

        return (new OperOrderExport($list))->download('订单列表.xlsx');
    }
}