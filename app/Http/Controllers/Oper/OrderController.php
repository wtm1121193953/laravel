<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23
 * Time: 22:56
 */

namespace App\Http\Controllers\Oper;


use App\Exports\OperOrderExport;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Dishes\DishesItem;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

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
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
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
        ], true);

        $list = $query->get();
        $merchantIds = $list->pluck('merchant_id');
        $merchants = Merchant::whereIn('id', $merchantIds->all())->get(['id', 'name'])->keyBy('id');
        $list->each(function($item) use ($merchants){
            $item->merchant_name = isset($merchants[$item->merchant_id]) ? $merchants[$item->merchant_id]->name : '';
            if ($item->type == 3){
                $dishesItems = DishesItem::where('dishes_id', $item->dishes_id)->get();
                $item->dishes_items = $dishesItems;
            }
        });

        return (new OperOrderExport($list))->download('订单列表.xlsx');
    }
}