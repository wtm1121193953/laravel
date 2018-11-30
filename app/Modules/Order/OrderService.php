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
use App\Exceptions\DataNotFoundException;
use App\Jobs\OrderPaidJob;
use App\Modules\Dishes\DishesGoods;
use App\Jobs\OrderFinishedJob;
use App\Modules\Dishes\DishesItem;
use App\Modules\Goods\Goods;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Payment\Payment;
use App\Modules\Platform\PlatformTradeRecord;
use App\Modules\Sms\SmsService;
use App\Modules\Oper\Oper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $bizerId = array_get($params, 'bizerId');
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

        $query = Order::where(function (Builder $query) {
            $query->where('type', Order::TYPE_GROUP_BUY)
                ->orWhere(function (Builder $query) {
                    $query->where('type', Order::TYPE_SCAN_QRCODE_PAY)
                        ->whereIn('status', [4, 6, 7]);
                })->orWhere(function (Builder $query) {
                    $query->where('type', Order::TYPE_DISHES);
                });
        });

        if ($userId > 0) {
            $query->where('user_id', $userId);
        }
        //if($merchantId > 0){
            //$query->where('merchant_id', $merchantId);
       // }
        if(!empty($merchantId)){
            if (is_array($merchantId) || $merchantId instanceof Collection) {
                $query->whereIn('merchant_id', $merchantId);
            } else {
                $query->where('merchant_id', $merchantId);
            }
        }
        if ($operId > 0) {
            $query->where('oper_id', $operId);
        }
        if ($bizerId > 0) {
            $query->where('bizer_id', $bizerId);
        }
        if ($orderNo) {
            $query->where('order_no', 'like', "%$orderNo%");
        }
        if ($notifyMobile) {
            $query->where('notify_mobile', 'like', "%$notifyMobile%");
        }
        if ($startCreatedAt && $endCreatedAt) {
            $query->whereBetween('created_at', [$startCreatedAt, $endCreatedAt]);
        } else if ($startCreatedAt) {
            $query->where('created_at', '>', $startCreatedAt);
        } else if ($endCreatedAt) {
            $query->where('created_at', '<', $endCreatedAt);
        }
        if ($startPayTime && $endPayTime) {
            $query->whereBetween('pay_time', [$startPayTime, $endPayTime]);
        } else if ($startPayTime) {
            $query->where('pay_time', '>', $startPayTime);
        } else if ($endPayTime) {
            $query->where('pay_time', '<', $endPayTime);
        }
        if ($startFinishTime && $endFinishTime) {
            $query->whereBetween('finish_time', [$startFinishTime, $endFinishTime]);
        } else if ($startFinishTime) {
            $query->where('finish_time', '>', $startFinishTime);
        } else if ($endFinishTime) {
            $query->where('finish_time', '<', $endFinishTime);
        }
        if ($type) {
            if (is_array($type)) {
                $query->whereIn('type', $type);
            } else {
                $query->where('type', $type);
            }
        }
        if ($status) {
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', $status);
            }
        } elseif (!empty($params['from_saas'])) {
            //saas只看 已支付、已完成、已退款的
            $query->whereIn('status', [Order::STATUS_PAID,Order::STATUS_REFUNDING,Order::STATUS_REFUNDED,Order::STATUS_FINISHED]);
        }
        if (!empty($params['platform_only'])) {
            $query->where('pay_target_type',2);
        }
        //if($type== 1 && $goodsName)
        if($goodsName){
            $query->where('goods_name', 'like', "%$goodsName%");
        }
        if ($keyword) {
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('order_no', 'like', "%$keyword%")
                    ->orWhere('notify_mobile', 'like', "%$keyword%");
            });
        }

        $query->with('oper:id,name');
        $query->orderBy('id', 'desc');

        if ($getWithQuery) {
            return $query;
        }

        $data = $query->paginate();

        $merchantIds = $data->pluck('merchant_id');
        $merchants = Merchant::whereIn('id', $merchantIds->all())->get(['id', 'name'])->keyBy('id');
        $payments = Payment::getAllType();
        foreach ($data as $key => $item) {
            $item->merchant_name = isset($merchants[$item->merchant_id]) ? $merchants[$item->merchant_id]->name : '';
            $item->operName = Oper::where('id', $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id)->value('name');
            $item->operId = $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id;
            if ($item->type == 3){
                $dishesItems = DishesItem::where('dishes_id', $item->dishes_id)->get();
                $data[$key]['dishes_items'] = $dishesItems;
            }
            $item->items = OrderItem::where('order_id', $item->id)->get();
            $item->goods_end_date = Goods::where('id', $item->goods_id)->value('end_date');
            $item->pay_type_name = $payments[$item->pay_type]??'未知('.$item->pay_type.')';
        }

        return $data;
    }

    /**
     * 获取支付到运营中心的结算单的订单列表
     * @param $settlementId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getListByOperSettlementId($settlementId)
    {
        return self::getListBySettlementId($settlementId, Order::PAY_TARGET_TYPE_OPER);
    }

    /**
     * 获取支付到平台的结算单的订单列表
     * @param $settlementId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getListByPlatformSettlementId($settlementId)
    {
        return self::getListBySettlementId($settlementId, Order::PAY_TARGET_TYPE_PLATFORM);
    }

    /**
     * 根据结算ID与支付目标类型获取订单列表
     * @param $settlementId int 结算ID
     * @param $payTargetType int 支付目标类型 1-支付给运营中心 2-支付给平台
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getListBySettlementId($settlementId, $payTargetType)
    {
        $data = Order::where('settlement_id', $settlementId)
            ->where('pay_target_type', $payTargetType)
            ->with('merchant:id,name')
            ->with('oper:id,name')
            ->orderBy('id', 'desc')
            ->paginate();
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

        if (!$order_id) {
            throw new BaseResponseException('该消费码不存在');
        }

        $order = Order::findOrFail($order_id);
        if ($order['status'] == Order::STATUS_FINISHED) {
            throw new BaseResponseException('该消费码已核销');
        }

        if ($order['status'] == Order::STATUS_PAID) {
            OrderItem::where('order_id', $order_id)
                ->where('merchant_id', request()->get('current_user')->merchant_id)
                ->update(['status' => 2]);

            $order->status = Order::STATUS_FINISHED;
            $order->finish_time = Carbon::now();
            $order->save();
            // 核销完订单之后 进行分润操作
            if($order->status == Order::STATUS_FINISHED){
                OrderFinishedJob::dispatch($order)->onQueue('order:finished');
            }

            return $order;
        } else {
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

    /**
     * @param $orderNo
     * @return Order
     */
    public static function getInfoByOrderNo($orderNo)
    {
        return Order::where('order_no', $orderNo)->firstOrFail();
    }

    /**
     * @param $orderId
     * @param array $fields
     * @return Order
     */
    public static function getById($orderId, $fields = ['*'])
    {
        return Order::find($orderId, $fields);
    }

    /**
     * 获取订单利润
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
        $taxAmount = $grossProfit * 0.06 * 1.12 / 1.06 + $grossProfit * 0.1 * 0.25 + 0.0068 * $order->pay_price;

        return max(floor(($grossProfit - $taxAmount) * 100) / 100, 0);
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

    /**
     * @param $orderNo
     * @param $transactionId
     * @param $totalFee
     * @param int $payType
     * @param Carbon|string $payTime 支付时间
     * @return bool
     * @throws \Exception
     */
    public static function paySuccess($orderNo, $transactionId, $totalFee, $payType = Order::PAY_TYPE_WECHAT, $payTime='')
    {
        if (empty($payTime)) {
            $payTime = Carbon::now();
        }
        // 处理订单支付成功逻辑
        $order = OrderService::getInfoByOrderNo($orderNo);

        Log::info('处理订单回调order数据',['order' => $order]);
        if($order->status === Order::STATUS_UN_PAY
            || $order->status === Order::STATUS_CANCEL
            || $order->status === Order::STATUS_CLOSED
        ){
            DB::beginTransaction();
            try{
                $order->pay_type = $payType;
                $order->pay_time = $payTime; // 更新支付时间为当前时间
                if($order->type == Order::TYPE_SCAN_QRCODE_PAY){
                    // 如果是扫码付款, 直接改变订单状态为已完成
                    $order->status = Order::STATUS_FINISHED;
                    $order->finish_time = Carbon::now();
                    $order->save();
                }else if($order->type == Order::TYPE_DISHES){
                    $order->status = Order::STATUS_FINISHED;
                    $order->finish_time = Carbon::now();
                    $order->save();
                }else {
                    $order->status = Order::STATUS_PAID;
                    $order->save();
                }

                if($order->type == Order::TYPE_GROUP_BUY){
                    // 添加商品已售数量
                    Goods::where('id', $order->goods_id)->increment('sell_number', max($order->buy_number, 1));
                    // 生成核销码, 线上需要放到支付成功通知中
                    $verify_code = OrderItem::createVerifyCode($order->merchant_id);
                    // todo 一个订单只生成一个核销码
                    for ($i = 0; $i < $order->buy_number; $i ++){
                        $orderItem = new OrderItem();
                        $orderItem->oper_id = $order->oper_id;
                        $orderItem->merchant_id = $order->merchant_id;
                        $orderItem->order_id = $order->id;
                        $orderItem->verify_code = $verify_code;
                        $orderItem->status = 1;
                        $orderItem->save();
                    }
                } else if($order->type == Order::TYPE_DISHES){
                    //添加菜单已售数量
                    $dishesItems = DishesItem::where('dishes_id',$order->dishes_id)->get();
                    foreach ($dishesItems as $k=>$item){
                        DishesGoods::where('id', $item->dishes_goods_id)->increment('sell_number', max($item->number, 1));
                    }
                }



                // 生成订单支付记录
                $orderPay = new OrderPay();
                $orderPay->order_id = $order->id;
                $orderPay->order_no = $orderNo;
                $orderPay->transaction_no = $transactionId;
                $orderPay->amount = $totalFee;
                $orderPay->save();

                //如果是支付到平台的订单，产生一条交易流水
                if ($order->pay_target_type != Order::PAY_TARGET_TYPE_OPER ) {
                    $platform_trade_record = new PlatformTradeRecord();
                    $platform_trade_record->type = PlatformTradeRecord::TYPE_PAY;
                    $platform_trade_record->pay_id = 1;
                    $platform_trade_record->trade_amount = $totalFee;
                    $platform_trade_record->trade_time = $payTime;
                    $platform_trade_record->trade_no = $transactionId;
                    $platform_trade_record->order_no = $orderNo;
                    $platform_trade_record->oper_id = $order->oper_id;
                    $platform_trade_record->merchant_id = $order->merchant_id;
                    $platform_trade_record->user_id = $order->user_id;
                    $platform_trade_record->remark = '';
                    $platform_trade_record->save();
                }

                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                Log::error('订单支付成功回调操作失败,失败信息:'.$e->getMessage());
                return false;
            }

            try {
                // 支付成功, 如果用户没有被邀请过, 将用户的邀请人设置为当前商户
                // 需要同步执行, 以防止执行订单完成任务的分润时, 商户还没绑定为用户的邀请人, 同时, 该操作执行失败不影响订单支付完成状态
                $userId = $order->user_id;
                if( empty( InviteUserRecord::where('user_id', $userId)->first() ) ){
                    $merchantId = $order->merchant_id;
                    $merchant = MerchantService::getById($merchantId);
                    $originType = InviteChannel::ORIGIN_TYPE_MERCHANT;

                    if(empty($merchant)){
                        throw new DataNotFoundException('商户信息不存在');
                    }
                    $inviteChannel = InviteChannelService::getByOriginInfo($merchantId, $originType, $merchant->oper_id);
                    InviteUserService::bindInviter($userId, $inviteChannel);
                }

            }catch (\Exception $e){
                // 此操作不影响流程, 捕捉异常之后不做其他操作
                Log::error('订单支付完成后, 绑定商户为用户的邀请人操作失败', compact('order', 'merchant'));
            }

            // 提交事务之后再派发任务, 防止任务处理时订单状态还未修改
            OrderPaidJob::dispatch($order);

            // 如果订单是已完成的状态, 分发一个订单完成的任务, 读写分离, 必须在修改状态的地方发任务, 否则可能会造成丢单
            if($order->status == Order::STATUS_FINISHED){
                OrderFinishedJob::dispatch($order)->onQueue('order:finished');
            }

            SmsService::sendBuySuccessNotify($orderNo);

            return true;
        }else if($order->status == Order::STATUS_PAID){
            // 已经支付成功了
            return true;
        }else if($order->status == Order::STATUS_REFUNDING
            || $order->status === Order::STATUS_REFUNDED
            || $order->status === Order::STATUS_FINISHED
        ){
            // 订单已退款或已完成
            return true;
        }
        return false;
    }
}