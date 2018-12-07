<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/17/017
 * Time: 15:34
 */
namespace App\Http\Controllers\Admin;

use App\Exports\AdminOrderExport;
use App\Exports\PlatformTradeRecordsExport;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesItem;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\Order\OrderService;
use App\Modules\Payment\Payment;
use App\Modules\Platform\PlatformTradeRecordService;
use App\Result;

class OrderController extends Controller
{
    public function getList()
    {
        $orderNo = request('orderNo');
        $mobile = request('mobile');
        $oper_id = request('oper_id');
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
            'operId' => $oper_id,
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
            'from_saas' => 1
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
        $oper_id = request('oper_id');
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

        $query = OrderService::getList([
            'operId' => $oper_id,
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
            'from_saas' => 1
        ], true);

        $list = $query->get();
        $merchantIds = $list->pluck('merchant_id');
        $merchants = Merchant::whereIn('id', $merchantIds->all())->get(['id', 'name'])->keyBy('id');
        $payments = Payment::getAllType();
        $list->each(function($item) use ($merchants,$payments){
            $item->merchant_name = isset($merchants[$item->merchant_id]) ? $merchants[$item->merchant_id]->name : '';
            $item->oper_name = Oper::where('id', $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id)->value('name');
            $item->pay_type_name = $payments[$item->pay_type]??'';
            if ($item->type == 3){
                $dishesItems = DishesItem::where('dishes_id', $item->dishes_id)->get();
                $item->dishes_items = $dishesItems;
            }
        });

        return (new AdminOrderExport($list))->download('订单列表.xlsx');
    }

    public function platformTradeRecords()
    {

        $params = [
            'order_no' => request('order_no'),
            'trade_no' => request('trade_no'),
            'oper_id' => request('oper_id'),
            'merchant_id' => request('merchant_id'),
            'startTime' => request('startTime'),
            'endTime' => request('endTime'),
            'user_id' => request('user_id'),
            'merchant_type' => request('merchant_type'),
        ];

        $data = PlatformTradeRecordService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function recordDetail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);

    }

    public function platformTradeRecordsExport()
    {

        $params = [
            'order_no' => request('order_no'),
            'trade_no' => request('trade_no'),
            'oper_id' => request('oper_id'),
            'merchant_id' => request('merchant_id'),
            'startTime' => request('startTime'),
            'endTime' => request('endTime'),
            'user_id' => request('user_id'),
            'merchant_type' => request('merchant_type'),
        ];

        $data = PlatformTradeRecordService::getList($params,true);
        return (new PlatformTradeRecordsExport($data, $params))->download(' 平台交易记录.xlsx');

    }
}