<?php

namespace App\Http\Controllers\Oper;


use App\Exports\OperOrderExport;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Dishes\DishesItem;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Result;

class OrderController extends Controller
{

    private static function getListQuery($withQuery = false)
    {
        $orderNo = request('orderNo');
        $mobile = request('mobile');
        $merchantId = request('merchantId');
        $timeType = request('timeType', 'payTime');
        $startTime = request('startTime');
        $endTime = request('endTime');
        $status = request('status');
        $type = request('type');
        $merchantType = request('merchantType', Order::MERCHANT_TYPE_NORMAL);

        if($timeType == 'payTime'){
            $startPayTime = $startTime;
            $endPayTime = $endTime;
        } elseif ($timeType == 'createdTime') {
            $startCreatedTime = $startTime;
            $endCreatedTime = $endTime;
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
            'startCreatedAt' => $startCreatedTime ?? null,
            'endCreatedAt' => $endCreatedTime ?? null,
            'status' => $status,
            'type' => $type,
            'merchantType' => $merchantType,
        ], $withQuery);

        return $data;
    }

    public function getList()
    {
        $data = self::getListQuery();

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
        $query = self::getListQuery(true);

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

    /**
     * 获取未发货超市订单的数量
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUndeliveredNum()
    {
        $params = [
            'operId' => request()->get('current_user')->oper_id,
            'status' => Order::STATUS_UNDELIVERED,
            'merchantType' => Order::MERCHANT_TYPE_SUPERMARKET,
        ];
        $query = OrderService::getList($params, true);
        $count = $query->count();

        return Result::success([
            'total' => $count,
        ]);
    }

    /**
     * oper 超市订单导出
     */
    public function csExport()
    {
        $query = self::getListQuery(true);

        $fileName = '超市订单列表';
        header('Content-Type: application/vnd.ms-execl');
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');

        $fp = fopen('php://output', 'a');
        $title = ['支付时间', '订单号', '历时', '订单类型', '商品名称', '总价（元）', '手机号码', '订单状态', '发货方式', '商户名称'];
        foreach ($title as $key => $value) {
            $title[$key] = iconv('UTF-8', 'GBK', $value);
        }
        fputcsv($fp, $title);

        $query->chunk(1000, function ($data) use ($fp) {
            foreach ($data as $key => $value) {
                $item = [];

                $item['pay_time'] = $value['pay_time'];
                $item['order_no'] = $value['order_no'];
                if (in_array($value['status'], [Order::STATUS_UNDELIVERED, Order::STATUS_NOT_TAKE_BY_SELF, Order::STATUS_DELIVERED])) {
                    $item['take_time'] = date('H时i分', time() - strtotime($value['pay_time']));
                } elseif ($value['status'] == Order::STATUS_FINISHED) {
                    $item['take_time'] = date('H时i分', time() - strtotime($value['pay_time']));
                } elseif ($value['status'] == Order::STATUS_REFUNDED) {
                    $item['take_time'] = date('H时i分', time() - strtotime($value['pay_time']));
                }
                $item['type'] = Order::getTypeText($value['type']);
                $item['goods_name'] = Order::getGoodsNameText($value['type'], $value['csOrderGoods'], $value['goods_name']);
                $item['pay_price'] = $value['pay_price'];
                $item['notify_mobile'] = $value['notify_mobile'];
                $item['status'] = Order::getStatusText($value['status']);
                $item['deliver_type'] = Order::getDeliverTypeText($value['deliver_type']);
                $item['merchant_name'] = $value['merchant_name'];

                foreach ($item as $k => $v) {
                    $item[$k] = iconv('UTF-8', 'GBK', $v);
                }
                fputcsv($fp, $item);
            }
            ob_flush();
            flush();
        });
    }
}