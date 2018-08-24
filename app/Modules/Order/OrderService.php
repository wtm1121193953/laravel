<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/25
 * Time: 16:16
 */

namespace App\Modules\Order;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Dishes\DishesItem;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrderService extends BaseService
{

    /**
     * 查询订单列表
     * @param array $params
     * @param bool $getWithQuery
     * @return Order|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $params, $getWithQuery = false)
    {
        $operId = array_get($params, 'operId');
        $userId = array_get($params, 'userId');
        $merchantId = array_get($params, 'merchantId');
        $orderNo = array_get($params, 'orderNo');
        $notifyMobile = array_get($params, 'notifyMobile');
        $keyword = array_get($params, 'keyword');
        $type = array_get($params, 'type');
        $status = array_get($params, 'status');
        $goodsName = array_get($params, 'goodsName');
        $startCreatedAt = array_get($params, 'startCreatedAt');
        $endCreatedAt = array_get($params, 'endCreatedAt');
        $startPayTime = array_get($params, 'startPayTime');
        $endPayTime = array_get($params, 'endPayTime');
        $startFinishTime = array_get($params, 'startFinishTime');
        $endFinishTime = array_get($params, 'endFinishTime');

        $query = Order::where(function(Builder $query){
            $query->where('type', Order::TYPE_GROUP_BUY)
                ->orWhere(function(Builder $query){
                    $query->where('type', Order::TYPE_SCAN_QRCODE_PAY)
                        ->whereIn('status', [4, 6, 7]);
                })->orWhere(function(Builder $query){
                    $query->where('type', Order::TYPE_DISHES);
                });
        });

        if($userId > 0){
            $query->where('user_id', $userId);
        }
        if($merchantId > 0){
            $query->where('merchant_id', $merchantId);
        }
        if($operId > 0){
            $query->where('oper_id', $operId);
        }
        if($orderNo){
            $query->where('order_no','like', "%$orderNo%");
        }
        if($notifyMobile){
            $query->where('notify_mobile', 'like', "%$notifyMobile%");
        }
        if($startCreatedAt && $endCreatedAt){
            $query->whereBetween('created_at', [$startCreatedAt, $endCreatedAt]);
        }else if($startCreatedAt){
            $query->where('created_at', '>', $startCreatedAt);
        }else if($endCreatedAt){
            $query->where('created_at', '<', $endCreatedAt);
        }
        if($startPayTime && $endPayTime){
            $query->whereBetween('pay_time', [$startPayTime, $endPayTime]);
        }else if($startPayTime){
            $query->where('pay_time', '>', $startPayTime);
        }else if($endPayTime){
            $query->where('pay_time', '<', $endPayTime);
        }
        if($startFinishTime && $endFinishTime){
            $query->whereBetween('finish_time', [$startFinishTime, $endFinishTime]);
        }else if($startFinishTime){
            $query->where('finish_time', '>', $startFinishTime);
        }else if($endFinishTime){
            $query->where('finish_time', '<', $endFinishTime);
        }
        if($type){
            if(is_array($type)){
                $query->whereIn('type',$type);
            }else {
                $query->where('type',$type);
            }
        }
        if($status){
            if(is_array($status)){
                $query->whereIn('status', $status);
            }else {
                $query->where('status', $status);
            }
        }
        if($type== 1 && $goodsName){
            $query->where('goods_name', 'like', "%$goodsName%");
        }
        if($keyword){
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('order_no', 'like', "%$keyword%")
                    ->orWhere('notify_mobile', 'like', "%$keyword%");
            });
        }

        $query->orderBy('id', 'desc');

        if ($getWithQuery) {
            return $query;
        }

        $data = $query->paginate();

        $merchantIds = $data->pluck('merchant_id');
        $merchants = Merchant::whereIn('id', $merchantIds->all())->get(['id', 'name'])->keyBy('id');
        foreach ($data as $key => $item){
            $item->merchant_name = isset($merchants[$item->merchant_id]) ? $merchants[$item->merchant_id]->name : '';
            if ($item->type == 3){
                $dishesItems = DishesItem::where('dishes_id', $item->dishes_id)->get();
                $data[$key]['dishes_items'] = $dishesItems;
            }
            $item->items = OrderItem::where('order_id', $item->id)->get();
            $item->goods_end_date = Goods::where('id', $item->goods_id)->value('end_date');
        }

        return $data;
    }

    /**
     * 核销订单
     * @param $merchantId
     * @param $verifyCode
     * @return Order
     */
    public static function verifyOrder($merchantId, $verifyCode)
    {

        $order_id = OrderItem::where('verify_code', $verifyCode)
            ->where('merchant_id', $merchantId)
            ->value('order_id');

        if(!$order_id){
            throw new BaseResponseException('该消费码不存在');
        }

        $order = Order::findOrFail($order_id);
        if($order['status'] == Order::STATUS_FINISHED){
            throw new BaseResponseException('该消费码已核销');
        }

        if($order['status'] == Order::STATUS_PAID){
            OrderItem::where('order_id', $order_id)
                ->where('merchant_id', request()->get('current_user')->merchant_id)
                ->update(['status' => 2]);

            $order->status = Order::STATUS_FINISHED;
            $order->finish_time = Carbon::now();
            $order->save();
            return $order;
        }else{
            throw new BaseResponseException('该订单已退款，不能核销');
        }
    }

    /**
     * 根据用户ID获取用户下单总数量
     * @param $userId
     * @return int
     */
    public static function getOrderCountByUserId($userId)
    {
        $count = Order::where('user_id', $userId)
            ->whereNotIn('status', [Order::STATUS_UN_PAY, Order::STATUS_CLOSED])
            ->count();
        return $count;
    }

    public static function getInfoByOrderNo($orderNo)
    {
        return Order::where('order_no', $orderNo)->firstOrFail();
    }

    public static function getById($orderId, $fields = ['*'])
    {
        return Order::find($orderId, $fields);
    }

    /**
     * 获取订单剩余利润
     * @param $order
     * @return float
     */
    public static function getProfitAmount($order)
    {
        if(is_int($order)){
            $order = self::getById($order);
        }
        $settlementRate = $order->settlement_rate; //分利比例
        // 分利比例要从订单中获取  $order->settlement_rate
        // 计算盈利金额
        $grossProfit = $order->pay_price * $settlementRate / 100;
        $taxAmount = $grossProfit * 0.06 * 1.12 / 1.06 + $grossProfit * 0.1 * 0.25 + 0.006 * $order->pay_price;

        return $grossProfit - $taxAmount;
    }

    /**
     * 更新分润状态 和 时间
     * @param $order
     * @return Order
     */
    public static function updateSplittingStatus($order)
    {
        if (is_int($order)) {
            $order = self::getById($order);
        }
        $order->splitting_status = Order::SPLITTING_STATUS_YES;
        $order->splitting_time = Carbon::now();
        $order->save();

        return $order;
    }

    /**
     * 获取退款单信息
     * @param $refundId
     * @param $fields
     * @return OrderRefund
     */
    public static function getRefundById($refundId, $fields = ['*'])
    {
        return OrderRefund::find($refundId, $fields);
    }

    /**
     * 生成退款单号
     * @param int $retry
     * @return string
     */
    public static function genRefundNo($retry = 1000)
    {
        if($retry == 0){
            throw new BaseResponseException('退款单号生成已超过最大重试次数');
        }
        $refundNo = 'R' . date('YmdHis') . rand(1000, 9999);
        if(OrderRefund::where('refund_no', $refundNo)->first()){
            $refundNo = self::genRefundNo(--$retry);
        }
        return $refundNo;
    }
}