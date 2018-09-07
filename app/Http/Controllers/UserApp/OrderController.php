<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 17:11
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Dishes\DishesItem;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderRefund;
use App\Modules\Order\OrderService;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditRecord;
use App\Modules\Wechat\WechatService;
use App\Result;
use App\Support\Alipay;
use Illuminate\Support\Facades\Log;
use App\Modules\Dishes\Dishes;
use App\Modules\Merchant\MerchantSettingService;
use App\Modules\Oper\Oper;

class OrderController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $user = request()->get('current_user');

        $data = OrderService::getList([
            'userId' => $user->id,
            'status' => $status
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail(){
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $user = request()->get('current_user');
        $detail = Order::where('order_no', request('order_no'))
            ->where('user_id', $user->id)
            ->firstOrFail();
        $detail->items = OrderItem::where('order_id', $detail->id)->get();

        $creditRecord = UserCreditRecord::where('order_no', $detail->order_no)
            ->where('type', 1)
            ->where('user_id', $user->id)
            ->first();
        if (!empty($creditRecord)){
            $detail->user_level = $creditRecord->user_level;
            $detail->user_level_text = User::getLevelText($creditRecord->user_level);
            $detail->credit = $creditRecord->credit;
        }
        return Result::success($detail);
    }

    /**
     * 订单创建
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
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

        $order = new Order();
        $orderNo = Order::genOrderNo();
        $order->oper_id = $merchant->oper_id;
        $order->order_no = $orderNo;
        $order->user_id = $user->id;
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
        $order->origin_app_type = request()->header('app-type');
        $order->save();

        return Result::success($order);
    }

    /**
     * 点菜订单创建
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Exception
     */
    public function dishesBuy()
    {
        $this->validate(request(), [
            'dishes_id' => 'required|integer|min:1',
        ]);
        $dishesId = request('dishes_id');
        $dishes = Dishes::findOrFail($dishesId);
        $userIdByDish = $dishes->user_id;
        $user = request()->get('current_user');
        $merchant = Merchant::findOrFail($dishes->merchant_id);
        $currentOperId = request()->get('current_oper_id');
        
        if ($userIdByDish != $user->id) {
            throw new ParamInvalidException('参数错误');
        }
        $result = MerchantSettingService::getValueByKey($dishes->merchant_id, 'dishes_enabled');
        if (!$result) {
            throw new BaseResponseException('单品购买功能尚未开启！');
        }
        //判断商品上下架状态
        $dishesItems = DishesItem::where('dishes_id', $dishesId)
            ->where('user_id', $dishes->user_id)
            ->get();
        foreach ($dishesItems as $item) {
            $dishesGoods = DishesGoods::findOrFail($item->dishes_goods_id);
            if ($dishesGoods->status == DishesGoods::STATUS_OFF) {
                throw new BaseResponseException('菜单已变更, 请刷新页面');
            }
        }

        $merchant_oper = Oper::findOrFail($merchant->oper_id);


        $order = new Order();
        $orderNo = Order::genOrderNo();
        $order->oper_id = $merchant->oper_id;
        $order->order_no = $orderNo;
        $order->user_id = $user->id;
        $order->open_id = request()->get('current_open_id');
        $order->user_name = $user->name ?? '';
        $order->type = Order::TYPE_DISHES;
        $order->notify_mobile = request('notify_mobile') ?? $user->mobile;
        $order->merchant_id = $merchant->id;
        $order->merchant_name = $merchant->name ?? '';
        $order->goods_name = $merchant->name ?? '';
        $order->dishes_id = $dishesId;
        $order->status = Order::STATUS_UN_PAY;
        $order->pay_price = $this->getTotalPrice();
        $order->settlement_rate = $merchant->settlement_rate;
        $order->remark = request('remark', '');
        $order->pay_target_type = $merchant_oper->pay_to_platform ? Order::PAY_TARGET_TYPE_PLATFORM : Order::PAY_TARGET_TYPE_OPER;
        empty($order->open_id)?$order->open_id = 'mock_open_id':$order->open_id=$order->open_id;
        $order->save();
        if ($order->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM) { // 如果是支付到平台
            if ($currentOperId == 0) { // 在平台小程序下
                // 调平台支付, 走融宝支付接口
                $isOperSelf = 1;
//                $sdkConfig = $this->_payToPlatform($order);
            } else {
                $isOperSelf = 0;
                $sdkConfig = null;
            }
        } else {
            $isOperSelf = $merchant->oper_id === $currentOperId ? 1 : 0;
            if ($isOperSelf == 1) {
//                $sdkConfig = $this->_wechatUnifyPayToOper($order);
            } else {
                $sdkConfig = null;
            }

        }


        return Result::success([
            'order_no' => $orderNo,
            'isOperSelf' => $isOperSelf,
            'sdk_config' => '',
            'order' => $order,
        ]);
    }

    /**
     * 获取总价格
     */
    public function getTotalPrice()
    {
        $dishesId = request('dishes_id');
        $list = DishesItem::where('dishes_id', $dishesId)->get();
        $totalPrice = 0;
        foreach ($list as $v) {
            $totalPrice += ($v->dishes_goods_sale_price) * ($v->number);
        }

        return $totalPrice;

    }

    /**
     * 扫码付款
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function scanQrcodePay()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'pay_type' => 'required',
        ]);
        $price = request('price');
        if($price <= 0 ){
            throw new ParamInvalidException('价格不合法');
        }
        $user = request()->get('current_user');
        $merchant = Merchant::findOrFail(request('merchant_id'));

        // 查询该用户在该商家下是否有未支付的直接付款订单, 若有直接修改原订单信息
        $order = Order::where('type', Order::TYPE_SCAN_QRCODE_PAY)
            ->where('merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('status', Order::STATUS_UN_PAY)
            ->first();
        if(empty($order)){
            $order = new Order();
            $orderNo = Order::genOrderNo();
            $order->order_no = $orderNo;
        }else {
            $orderNo = $order->order_no;
        }

        $order->oper_id = $merchant->oper_id;
        $order->user_id = $user->id;
        $order->user_name = $user->name ?? '';
        $order->notify_mobile = request('notify_mobile') ?? $user->mobile;
        $order->merchant_id = $merchant->id;
        $order->merchant_name = $merchant->name ?? '';
        $order->type = Order::TYPE_SCAN_QRCODE_PAY;
        $order->goods_id = 0;
        $order->goods_name = $merchant->name;
        $order->goods_pic = $merchant->logo;
        $order->price = $price;
        $order->status = Order::STATUS_UN_PAY;
        $order->pay_price = $price;

        $payType = request('pay_type', 1);
        $order->pay_type = $payType;
        $order->save();

        if($payType == 1){
            // 如果是微信支付
            // todo 微信支付暂时跳过
//            $sdkConfig = $this->_wechatUnifyPay($order);
            $sdkConfig = [];
            return Result::success([
                'order' => $order,
                'order_no' => $orderNo,
                'sdk_config' => $sdkConfig,
            ]);
        }else {
            // 如果是支付宝支付
            $sdkConfig = Alipay::pay($order);
            return Result::success([
                'order' => $order,
                'order_no' => $orderNo,
                'alipay_sdk_config' => $sdkConfig,
            ]);
        }
    }

    /**
     * 立即付款
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function pay()
    {
        $this->validate(request(), [
            'order_no' => 'required',
            'pay_type' => 'required',
        ]);
        $orderNo = request('order_no');
        $order = Order::where('order_no', $orderNo)->firstOrFail();

        if($order->status == Order::STATUS_PAID){
            throw new ParamInvalidException('该订单已支付');
        }
        if($order->status == Order::STATUS_CANCEL){
            throw new ParamInvalidException('该订单已取消');
        }
        if($order->status != Order::STATUS_UN_PAY){
            throw new BaseResponseException('订单状态异常');
        }

        $payType = request('pay_type', 1);
        $order->pay_type = $payType;
        $order->save();
        if($payType == 1){
            // 如果是微信支付
            // todo 暂时跳过微信支付
//            $sdkConfig = $this->_wechatUnifyPay($order);
            $sdkConfig = [];
            return Result::success([
                'order_no' => $orderNo,
                'sdk_config' => $sdkConfig,
            ]);
        }else {
            // 如果是支付宝支付
            $sdkConfig = Alipay::pay($order);
            return Result::success([
                'order_no' => $orderNo,
                'alipay_sdk_config' => $sdkConfig,
            ]);
        }
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
        if($order->pay_type == 1){
            // todo 暂时跳过支付, 直接返回成功
            // 微信退款成功
            $orderRefund->refund_id = 'mock refund id';
            $orderRefund->status = 2;
            $orderRefund->save();

            $order->status = Order::STATUS_REFUNDED;
            $order->save();
            return Result::success($orderRefund);

            // 发起微信支付退款
            // todo 获取平台的微信支付实例
            $payApp = WechatService::getWechatPayAppForOper(0);
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
                $this->decSellNumber($order);
                return Result::success($orderRefund);
            }else {
                Log::error('微信退款失败 :', [
                    'result' => $result,
                    'params' => [
                        'orderPay' => $orderPay->toArray(),
                        'orderRefund' => $orderRefund->toArray(),
                    ]
                ]);
                throw new BaseResponseException('微信退款失败');
            }
        }else {
            $result = Alipay::refund($orderPay, $orderRefund);
            if (!empty($result->code)&& $result->code == 10000){
                // 支付宝退款成功
                $orderRefund->refund_id = '';
                $orderRefund->status = 2;
                $orderRefund->save();

                $order->status = Order::STATUS_REFUNDED;
                $order->save();
                $this->decSellNumber($order);
                return Result::success($orderRefund);
            }else{
                Log::error('支付宝退款失败 :', [
                    'result' => $result,
                    'params' => [
                        'orderPay' => $orderPay->toArray(),
                        'orderRefund' => $orderRefund->toArray(),
                    ]
                ]);
                throw new BaseResponseException('支付宝退款失败');
            }
        }
    }

    /**
     * 微信下单并获取支付参数
     * @param $order
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    private function _wechatUnifyPay(Order $order)
    {
        // todo 获取平台的微信支付实例
        $payApp = WechatService::getWechatPayAppForOper($order->oper_id);
        $data = [
            'body' => $order->goods_name,
            'out_trade_no' => $order->order_no,
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
        $sdkConfig = $payApp->jssdk->appConfig($unifyResult['prepay_id']);
        return $sdkConfig;
    }

    /**
     * 订单支付到平台, 返回微信支付参数
     * @param $order
     * @return null|array
     * @throws \Exception
     */
    private function _payToPlatform($order)
    {
        $sdkConfig = null;
        //OrderService::paySuccess($order->order_no, 'pay_to_platform', $order->pay_price,Order::PAY_TYPE_WECHAT);

        $payApp = WechatService::getWechatPayAppForPlatform();
        $data = [
            'body' => $order->goods_name,
            'out_trade_no' => $order->order_no,
            'total_fee' => $order->pay_price * 100,
            'trade_type' => 'JSAPI',
            'openid' => request()->get('current_open_id'),
        ];
        $unifyResult = $payApp->order->unify($data);
        if(!($unifyResult['return_code'] === 'SUCCESS' && array_get($unifyResult, 'result_code') === 'SUCCESS')){
            Log::error('微信统一下单失败', [
                'payConfig' => $payApp->getConfig(),
                'data' => $data,
                'result' => $unifyResult,
            ]);
            throw new BaseResponseException('微信统一下单失败');
        }
        $sdkConfig = $payApp->jssdk->sdkConfig($unifyResult['prepay_id']);
        return $sdkConfig;

        // 调平台支付, 走融宝支付接口
        /*
        $sdkConfig = null; // todo 走融宝支付接口

        $result = $this->reapalPrepay($order);
        $sdkConfig = json_decode($result['wxjsapi_str'],true);
        $sdkConfig['timestamp'] = $sdkConfig['timeStamp'];
        */

        return $sdkConfig;

    }

    /**
     * 微信下单并获取支付参数, 支付到运营中心
     * @param $order
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    private function _wechatUnifyPayToOper(Order $order)
    {
        $payApp = WechatService::getWechatPayAppForOper($order->oper_id);
        $data = [
            'body' => $order->goods_name,
            'out_trade_no' => $order->order_no,
            'total_fee' => $order->pay_price * 100,
            'trade_type' => 'JSAPI',
            'openid' => request()->get('current_open_id'),
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
        return $sdkConfig;
    }

    /**
     * 退款返还商品数量
     * @param $order
     */
    private function decSellNumber($order)
    {
        if ($order->type == Order::TYPE_GROUP_BUY){
            Goods::where('id', $order->goods_id)
                ->where('merchant_id', $order->merchant_id)
                ->decrement('sell_number', $order->buy_number);
        }elseif ($order->type == Order::TYPE_DISHES){
            $dishesItems = DishesItem::where('merchant_id', $order->merchant_id)
                ->where('dishes_id', $order->dishes_id)
                ->get();
            foreach ($dishesItems as $item){
                DishesGoods::where('id', $item->dishes_goods_id)
                    ->where('merchant_id', $item->merchant_id)
                    ->decrement('sell_number', $item->number);
            }
        }
    }
}