<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/18
 * Time: 15:29
 */

namespace App\Modules\Sms;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Dishes\DishesItem;
use App\Modules\Goods\Goods;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\ResultCode;
use App\Support\MicroServiceApi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SmsVerifyCodeService extends BaseService
{

    /**
     * @param $mobile
     * @return SmsVerifyCode
     */
    public static function add($mobile)
    {
        $verifyCode = rand(1000, 9999);
        $record = new SmsVerifyCode();
        $record->mobile = $mobile;
        $record->verify_code = $verifyCode;
        $record->type = 1;
        $record->expire_time = Carbon::now()->addMinutes(10);
        $record->save();
        return $record;
    }

    /**
     * 发送短信
     * @param $mobile
     * @param $verifyCode
     * @return mixed|string
     */
    public static function sendVerifyCode($mobile, $verifyCode)
    {
        $content = self::parseTemplate(config('sms.template.verify_code'), [
            'verifyCode' => $verifyCode,
        ]);
        $result = MicroServiceApi::sendVerifyCodeV2($mobile, $content);
        return $result;
    }

    /**
     * 验证短信验证码
     * @param $mobile
     * @param $verifyCode
     * @return SmsVerifyCode|bool
     */
    public static function checkVerifyCode($mobile, $verifyCode)
    {
        if(App::environment('production') || $verifyCode != '6666'){
            $verifyCodeRecord = SmsVerifyCode::where('mobile', $mobile)
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('expire_time', '>', Carbon::now())
                ->first();
            if(empty($verifyCodeRecord)){
                return false;
            }
            $verifyCodeRecord->status = 2;
            $verifyCodeRecord->save();
            return $verifyCodeRecord;
        }
        return true;
    }

    /**
     * 购买成功发送通知
     * @param $orderNo
     * @return bool
     */
    public static function sendBuySuccessNotify($orderNo)
    {
        $order = Order::where('order_no', $orderNo)->firstOrFail();
        if ($order->type == Order::TYPE_GROUP_BUY) {
            $params = self::getGoodsBuySuccessNotifyParams($order);
            $content = self::parseTemplate(config('sms.template.group_buy'), $params);
            return MicroServiceApi::sendNotifyV2($order->notify_mobile, $content);
        }elseif ($order->type == Order::TYPE_DISHES) {
            $params = self::getDishesBuySuccessNotifyParams($order);
            $content = self::parseTemplate(config('sms.template.dishes_buy'), $params);
            return MicroServiceApi::sendNotifyV2($order->notify_mobile, $content);
        }else {
            Log::info('该订单类型不发送通知短信', ['order' => $order]);
            return false;
        }
    }

    /**
     * 团购商品 获取短信通知参数
     * @param Order $order
     * @return array
     */
    private static function getGoodsBuySuccessNotifyParams(Order $order)
    {
        // 团购商品
        $name = mb_strlen($order->goods_name) > 20 ? mb_substr($order->goods_name, 0, 19). '…' : $order->goods_name;
        $number = $order->buy_number;
        $endDate = Goods::where('id', $order->goods_id)->value('end_date');
        $verifyCode = OrderItem::where('order_id', $order->id)
            ->value('verify_code');
        $params = [
            'orderNo' => substr($order->order_no, 0, 6). '…'. substr($order->order_no, -6),
            'name' => $name,
            'number' => $number,
            'endDate' => $endDate,
            'verifyCode' => $verifyCode,
        ];
        return $params;
    }

    /**
     * 单品 获取短信通知参数
     * @param $order
     * @return array
     */
    private static function getDishesBuySuccessNotifyParams($order)
    {
        // 单品
        $name = DishesItem::where('dishes_id', $order->dishes_id)
            ->value('dishes_goods_name');
        $name = mb_strlen($name) > 20 ? mb_substr($name, 0, 19). '…' : $name;
        $dishesItems = DishesItem::where('dishes_id', $order->dishes_id)->get();
        $number = 0;
        foreach ($dishesItems as $dishesItem){
            $number += $dishesItem->number;
        }
        $params = [
            'orderNo' => substr($order->order_no, 0, 6). '…'. substr($order->order_no, -6),
            'name' => $name,
            'number' => $number,
        ];
        return $params;
    }

    public static function parseTemplate(string $template, array $params) : string
    {
        foreach ($params as $key => $value) {
            $template = str_replace("{" . $key . "}", $value, $template);
        }
        return $template;
    }
}