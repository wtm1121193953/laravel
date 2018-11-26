<?php

namespace App\Http\Controllers\Cs;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Imports\BatchDelivery;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Result;
use Maatwebsite\Excel\Facades\Excel;

class OrdersController extends Controller
{
    private static function getListQuery($withQuery = false)
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $orderNo = request('orderNo');
        $mobile = request('mobile');
        $timeType = request('timeType', 'payTime');
        $startTime = request('startTime');
        $endTime = request('endTime');
        $status = request('status');
        $type = request('type');
        $merchantType = request('merchantType', Order::MERCHANT_TYPE_SUPERMARKET);

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

    /**
     * 获取超市订单列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $data = self::getListQuery();

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 导出超市订单列表
     */
    public function export()
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

    /**
     * 检查超市订单 取货码
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDeliverCode()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'deliver_code' => 'required|max:6',
        ]);
        $id = request('id');
        $deliver_code = request('deliver_code');

        $order = OrderService::checkDeliverCode($id, $deliver_code);

        return Result::success($order);
    }

    /**
     * 超市订单核销
     * @return \Illuminate\Http\JsonResponse
     */
    public function verification()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'deliver_code' => 'required|max:6',
        ]);
        $id = request('id');
        $deliver_code = request('deliver_code');

        $order = OrderService::verifyCsOrder($id, $deliver_code);

        return Result::success($order);
    }

    /**
     * 获取待发货 待自提 的总数
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderFieldStatistics()
    {
        $undeliveredNum = OrderService::getList([
            'merchantId' => request()->get('current_user')->merchantId,
            'status' => Order::STATUS_UNDELIVERED,
            'merchantType' => Order::MERCHANT_TYPE_SUPERMARKET,
            'type' => Order::TYPE_SUPERMARKET,
        ], true)->count();

        $notTakeBySelfNum = OrderService::getList([
            'merchantId' => request()->get('current_user')->merchantId,
            'status' => Order::STATUS_NOT_TAKE_BY_SELF,
            'merchantType' => Order::MERCHANT_TYPE_SUPERMARKET,
            'type' => Order::TYPE_SUPERMARKET,
        ], true)->count();

        return Result::success([
            'undeliveredNum' => $undeliveredNum,
            'notTakeBySelfNum' => $notTakeBySelfNum,
        ]);
    }

    /**
     * 超市订单发货
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderDeliver()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'expressCompany' => 'required|max:50',
            'expressNo' => 'required|max:50',
        ]);

        $id = request('id');
        $expressCompany = request('expressCompany');
        $expressNo = request('expressNo');

        $order = OrderService::deliver($id, $expressCompany, $expressNo);

        return Result::success($order);
    }

    /**
     * 获取批量发货模板
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBatchDeliveryTemplatePath()
    {
        $path = storage_path('app/help-doc/batch_delivery_template.xlsx');
        return Result::success(['url' => $path]);
    }

    /**
     * 后台 批量发货
     */
    public function batchDelivery()
    {
        $file = request()->file('file');
        $merchantId = request()->get('current_user')->merchant_id;

        Excel::import(new BatchDelivery($merchantId), $file, null, ucfirst($file->getClientOriginalExtension()));

        return Result::success();
    }
}