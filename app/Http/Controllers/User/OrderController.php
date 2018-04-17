<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 17:11
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderRefund;
use App\Modules\Wechat\WechatService;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $user = request()->get('current_user');
        $data = Order::where('user_id', $user->id)
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate();
        $currentOperId = request()->get('current_oper')->id;
        $data->each(function ($item) use ($currentOperId) {
            $item->items = OrderItem::where('order_id', $item->id)->get();
            // 判断商户是否是当前小程序关联运营中心下的商户
            $item->isOperSelf = $item->oper_id === $currentOperId ? 1 : 0;
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail(){
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $detail = Order::where('order_no', request('order_no'))->firstOrFail();
        $detail->items = OrderItem::where('order_id', $detail->id)->get();
        $currentOperId = request()->get('current_oper')->id;
        // 判断商户是否是当前小程序关联运营中心下的商户
        $detail->isOperSelf = $detail->oper_id === $currentOperId ? 1 : 0;
        return Result::success($detail);
    }

    /**
     * 订单创建
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function buy()
    {
        $this->validate(request(), [
            'goods_id' => 'required|integer|min:1',
            'number' => 'integer|min:1',
        ]);
        $goodsId = request('goods_id');
        $number = request('number', 1);
        $goods = Goods::findOrFail($goodsId);

        $user = request()->get('current_user');

        $merchant = Merchant::findOrFail($goods->merchant_id);
        $oper = request()->get('current_oper');

        $order = new Order();
        $orderNo = Order::genOrderNo();
        $order->oper_id = $oper->id;
        $order->order_no = $orderNo;
        $order->user_id = $user->id;
        $order->open_id = request()->get('current_open_id');
        $order->user_name = $user->name ?? '';
        $order->notify_mobile = request('notify_mobile') ?? $user->mobile;
        $order->merchant_id = $merchant->id;
        $order->merchant_name = $merchant->name ?? '';
        $order->goods_id = $goodsId;
        $order->goods_name = $goods->name;
        $order->goods_pic = $goods->pic;
        $order->goods_thumb_url = $goods->thumb_url;
        $order->price = $goods->price;
        $order->buy_number = $number;
        $order->status = Order::STATUS_UN_PAY;
        $order->pay_price = $goods->price * $number;
        $order->save();

        $isOperSelf = $merchant->oper_id === $oper->id ? 1 : 0;
        if($isOperSelf == 1) {
            $payApp = WechatService::getWechatPayAppForOper($merchant->oper_id);
            $data = [
                'body' => $order->goods_name,
                'out_trade_no' => $orderNo,
                'total_fee' => $order->pay_price * 100,
                'trade_type' => 'JSAPI',
                'openid' => $order->open_id,
            ];
            $unifyResult = $payApp->order->unify($data);
            if($unifyResult['return_code'] === 'SUCCESS' && array_get($unifyResult, 'result_code') === 'SUCCESS'){
                $order->save();
            }else {
                Log::error('微信统一下单失败', [
                    'payConfig' => $payApp->getConfig(),
                    'data' => $data,
                    'result' => $unifyResult,
                ]);
                throw new BaseResponseException('微信统一下单失败');
            }
            $sdkConfig = $payApp->jssdk->sdkConfig($unifyResult['prepay_id']);
        }else {
            $sdkConfig = null;
        }

        if(App::environment() === 'local'){
            // 生成核销码, 线上需要放到支付成功通知中
            $items = [];
            for ($i = 0; $i < $number; $i ++){
                $orderItem = new OrderItem();
                $orderItem->oper_id = $oper->id;
                $orderItem->merchant_id = $merchant->id;
                $orderItem->order_id = $order->id;
                $orderItem->verify_code = OrderItem::createVerifyCode($merchant->id);
                $orderItem->status = 1;
                $orderItem->save();
                $items[] = $orderItem;
            }
            $order->status = Order::STATUS_PAID;
            $order->save();
        }

        return Result::success([
            'order_no' => $orderNo,
            'isOperSelf' => $isOperSelf,
            'sdk_config' => $sdkConfig
        ]);
    }

    /**
     * 立即付款
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function pay()
    {
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $orderNo = request('order_no');
        $order = Order::where('order_no', $orderNo)->firstOrFail();

        if($order->status != Order::STATUS_UN_PAY){
            throw new BaseResponseException('订单状态异常');
        }

        if($order->oper_id !== request()->get('current_oper')->id){
            throw new BaseResponseException('该订单不是当前运营中心的订单');
        }

        $payApp = WechatService::getWechatPayAppForOper($order->oper_id);
        $data = [
            'body' => $order->goods_name,
            'out_trade_no' => $orderNo,
            'total_fee' => $order->pay_price * 100,
            'trade_type' => 'JSAPI',
            'openid' => $order->open_id,
        ];

        $unifyResult = $payApp->order->unify($data);
        if($unifyResult['return_code'] === 'SUCCESS' && array_get($unifyResult, 'result_code') === 'SUCCESS'){
            $order->save();
        }else {
            Log::error('微信统一下单失败', [
                'payConfig' => $payApp->getConfig(),
                'data' => $data,
                'result' => $unifyResult,
            ]);
            throw new BaseResponseException('微信统一下单失败');
        }

        if(App::environment() === 'local'){
            // 生成核销码, 线上需要放到支付成功通知中
            $items = [];
            for ($i = 0; $i < $order->buy_number; $i ++){
                $orderItem = new OrderItem();
                $orderItem->oper_id = $order->oper_id;
                $orderItem->merchant_id = $order->merchant_id;
                $orderItem->order_id = $order->id;
                $orderItem->verify_code = OrderItem::createVerifyCode($order->merchant_id);
                $orderItem->status = 1;
                $orderItem->save();
                $items[] = $orderItem;
            }
            $order->status = Order::STATUS_PAID;
            $order->save();
        }

        $sdkConfig = $payApp->jssdk->sdkConfig($unifyResult['prepay_id']);

        return Result::success([
            'order_no' => $orderNo,
            'sdk_config' => $sdkConfig
        ]);
    }

    /**
     * 订单退款
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function refund()
    {
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $orderNo = request('order_no');
        $order = Order::where('order_no', $orderNo)->firstOrFail();
        if($order->status != Order::STATUS_PAID){
            throw new BaseResponseException('订单状态不允许退款');
        }
        // 查询支付记录
        $orderPay = OrderPay::where('order_id', $order->id)->firstOrFail();
        // 生成退款单
        $orderRefund = new OrderRefund();
        $orderRefund->order_id = $order->id;
        $orderRefund->order_no = $order->order_no;
        $orderRefund->amount = $orderPay->amount;
        $orderRefund->save();
        // 发起微信支付退款
        $payApp = WechatService::getWechatPayAppForOper(request()->get('current_oper')->id);
        $result = $payApp->refund->byTransactionId($orderPay->transaction_no, $orderRefund->id, $orderPay->amount * 100, $orderPay->amount * 100, [
            'refund_desc' => '用户发起退款',
        ]);
        if($result['return_code'] === 'SUCCESS' && array_get($result, 'result_code') === 'SUCCESS'){
            // 微信退款成功
            $orderRefund->refund_id = $result['refund_id'];
            $orderRefund->status = 2;
            $orderRefund->save();

            $order->status = Order::STATUS_REFUNDED;
            $order->save();
            return Result::success($orderRefund);
        }else {
            Log::error('微信退款失败 :', [
                'result' => $result,
                'params' => [
                    '$orderPay->transaction_no' => $orderPay->transaction_no,
                    '$orderRefund->id' => $orderRefund->id,
                    '$orderPay->amount' => $orderPay->amount,
                    'refundAmount' => $orderPay->amount
                ]
            ]);
            throw new BaseResponseException('微信退款失败');
        }
    }
}