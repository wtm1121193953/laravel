<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/15
 * Time: 16:24
 */

namespace App\Http\Controllers;


use App\Jobs\OrderPaidJob;
use App\Modules\Goods\Goods;
use App\Modules\Dishes\DishesItem;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\OperMiniprogram;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\Order\OrderPay;
use App\Modules\Sms\SmsService;
use App\Modules\Wechat\WechatService;
use App\Result;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{

    /**
     * 支付通知接口, 用于接收微信支付的通知
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function notify()
    {
        $str = request()->getContent();
        $xml = simplexml_load_string($str);
        // 获取appid
        foreach ($xml->children() as $child) {
            if(strtolower($child->getName()) == 'appid'){
                $appid = $child . '';
            }
        }
        // 获取appid对应的运营中心小程序
        $miniprogram = OperMiniprogram::where('appid', $appid)->first();

        $app = WechatService::getWechatPayAppForOper($miniprogram);
        $response = $app->handlePaidNotify(function ($message, $fail){
            if($message['return_code'] === 'SUCCESS' && array_get($message, 'result_code') === 'SUCCESS'){
                $orderNo = $message['out_trade_no'];
                $this->paySuccess($orderNo, $message['transaction_id'], $message['total_fee']);
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            // 其他未知情况
            return false;
        });
        return $response;
    }

    /**
     * 本地模拟支付成功
     */
    public function mockPaySuccess()
    {
        if(App::environment() === 'local' || App::environment() === 'test'){
            $this->paySuccess(request('order_no'), 'mock transaction id', 0);
            return Result::success('模拟支付成功');
        }else {
            abort(404);
        }
    }

    /**
     * 支付成功
     * @param $orderNo
     * @param $transactionId
     * @param $totalFee
     * @return bool
     */
    private function paySuccess($orderNo, $transactionId, $totalFee)
    {
        // 处理订单支付成功逻辑
        $order = Order::where('order_no', $orderNo)->firstOrFail();

        if($order->status === Order::STATUS_UN_PAY
            || $order->status === Order::STATUS_CANCEL
            || $order->status === Order::STATUS_CLOSED
        ){
            try{
                DB::beginTransaction();
                $order->pay_time = Carbon::now(); // 更新支付时间为当前时间
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
                $orderPay->amount = $totalFee * 1.0 / 100;
                $orderPay->save();

                // 支付成功, 如果用户没有被邀请过, 将用户的邀请人设置为当前商户
                $userId = $order->user_id;
                if( empty( InviteUserRecord::where('user_id', $userId)->first() ) ){
                    $merchantId = $order->merchant_id;
                    $merchant = Merchant::findOrFail($merchantId);
                    $inviteChannel = InviteChannelService::getInviteChannel($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT, $merchant->oper_id);
                    InviteService::bindInviter($userId, $inviteChannel);
                }
                OrderPaidJob::dispatch($order);
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                Log::error('订单支付成功回调操作失败,失败信息:'.$e->getMessage());
                return false;
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