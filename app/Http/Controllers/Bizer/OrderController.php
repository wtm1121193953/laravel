<?php

namespace App\Http\Controllers\Bizer;

use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Result;

class OrderController extends Controller
{
     function index(){
         
     }
    public function getList()
    {
        $where = [
            "orderNo"=>request('orderId'),
            "type"=>request('order_type'),
            "goodsName"=>request('goodsName'),
            "merchantId"=>request('merchantId'),
            "operId"=>request('operId'),
            'startCreatedAt' => request('startTime'),
            'endCreatedAt' => request('endTime'),
            'bizerId' => request()->get('current_user')->id,
        ];

        $data = OrderService::getList($where);
        $list = $data->items();
        $total= $data->total();

        return Result::success([
            'list' => $list,
            'total' => $total,
        ]);
    }

     /**
     * 获取业务员全部的商户名称
     */
    public function allMerchantNames()
    {
        $data = [
            'bizer_id'=>request()->get('current_user')->id,//登录所属业务员ID
            'audit_status' => request('audit_status'),
            'status' => request('status'),
        ];
        $list = MerchantService::getAllNames($data);
        return Result::success([
            'list' => $list
        ]);
    }

    /**
     * 导出业务员的订单列表
     */
    public function export()
    {
        $startCreatedAt = request('startTime', '');
        $endCreatedAt = request('endTime', '');
        $orderNo = request('orderNo', '');
        $type = request('order_type', '');
        $merchantId = request('merchantId', '');
        $operId = request('operId', '');
        $goodsName = request('goodsName', '');
        $bizerId = request()->get('current_user')->id;

        $params = compact('startCreatedAt', 'endCreatedAt', 'orderNo', 'type', 'merchantId', 'operId', 'goodsName', 'bizerId');
        $query = OrderService::getList($params, true);

        $fileName = '订单列表';
        header('Content-Type: application/vnd.ms-execl');
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');

        $fp = fopen('php://output', 'a');
        $title = ['创建时间', '订单号', '订单类型', '商品名称', '总价（元）', '手机号', '订单状态', '商户名称', '所属运营中心名称'];
        foreach ($title as $key => $value) {
            $title[$key] = iconv('UTF-8', 'GBK', $value);
        }
        fputcsv($fp, $title);

        $query->chunk(1000, function ($data) use ($fp) {
            foreach ($data as $key => $value) {
                $item = [];
                $item['created_at'] = $value['created_at'];
                $item['order_no'] = $value['order_no'];
                $item['type'] = ['', '团购订单', '扫码订单', '单品订单'][$value['type']];
                if ($value['type'] == Order::TYPE_DISHES) {
                    $num = 0;
                    $dishesItems = DishesService::getDishesItemsByDishesId($value['dishes_id']);
                    foreach ($dishesItems as $dishes_item) {
                        $num += $dishes_item['number'];
                    }
                    $item['goods_name'] = $dishesItems[0]['dishes_goods_name'] . '等' . $num . '件商品';
                } elseif ($value['type'] == Order::TYPE_SCAN_QRCODE_PAY) {
                    $item['goods_name'] = '无';
                } else {
                    $item['goods_name'] = $value['goods_name'];
                }
                $item['pay_price'] = $value['pay_price'];
                $item['notify_mobile'] = $value['notify_mobile'];
                $item['status'] = ['', '未支付', '已取消', '已关闭[超时自动关闭]', '已支付', '退款中[保留状态]', '已退款', '已完成'][$value['status']];
                $item['merchant_name'] = $value['merchant_name'];
                $item['operName'] = OperService::getNameById($value['oper_id']);
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