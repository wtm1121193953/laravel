<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 17:11
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Goods\Goods;
use App\Modules\Dishes\Dishes;
use App\Modules\Dishes\DishesItem;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\MerchantSettingService;
use App\Modules\Oper\Oper;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderRefund;

use App\Modules\Order\OrderService;
use App\Modules\Setting\SettingService;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditRecord;
use App\Modules\Wechat\WechatService;
use App\Result;
use App\Support\Reapal\ReapalPay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $user = request()->get('current_user');

        $merchantShareInMiniprogram = SettingService::getValueByKey('merchant_share_in_miniprogram');

        $currentOperId = request()->get('current_oper_id');
        $data = Order::where('user_id', $user->id)
            ->where(function (Builder $query) {
                $query->where('type', Order::TYPE_GROUP_BUY)
                    ->orWhere(function (Builder $query) {
                        $query->where('type', Order::TYPE_SCAN_QRCODE_PAY)
                            ->whereIn('status', [4, 6, 7]);
                    })->orWhere('type', Order::TYPE_DISHES);
            })
            ->when($merchantShareInMiniprogram != 1, function (Builder $query) use ($currentOperId) {
                $query->where('oper_id', $currentOperId);
            })
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate();
        $data->each(function ($item) use ($currentOperId) {
            $item->items = OrderItem::where('order_id', $item->id)->get();
            // 判断商户是否是当前小程序关联运营中心下的商户
            $item->isOperSelf = $item->oper_id === $currentOperId ? 1 : 0;
            $item->goods_end_date = Goods::withTrashed()->where('id', $item->goods_id)->value('end_date');
            $item->merchant_logo = Merchant::where('id', $item->merchant_id)->value('logo');
            $item->signboard_name = Merchant::where('id', $item->merchant_id)->value('signboard_name');
            if ($item->type == Order::TYPE_DISHES) {
                $item->dishes_items = DishesItem::where('dishes_id', $item->dishes_id)->get();
            }
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $detail = Order::where('order_no', request('order_no'))->firstOrFail();
        // 只返回一个核销码
        $orderItem = OrderItem::where('order_id', $detail->id)->first();
        $detail->items = !empty($orderItem) ? [$orderItem] : [];
        $currentOperId = request()->get('current_oper_id');
        // 判断商户是否是当前小程序关联运营中心下的商户
        if(!$currentOperId){ // 如果是在平台小程序下
            if($detail->pay_target_type == Order::PAY_TARGET_TYPE_OPER){
                $detail->isOperSelf = 0;
            }else {
                $detail->isOperSelf = 1;
            }
        }else {
            $detail->isOperSelf = $detail->oper_id === $currentOperId ? 1 : 0;
        }

        $detail->signboard_name = Merchant::where('id', $detail->merchant_id)->value('signboard_name');
        $creditRecord = UserCreditRecord::where('order_no', $detail->order_no)
            ->where('type', 1)
            ->first();
        if (!empty($creditRecord)) {
            $detail->user_level = $creditRecord->user_level;
            $detail->user_level_text = User::getLevelText($creditRecord->user_level);
            $detail->credit = $creditRecord->credit;
        }
        if ($detail->type == Order::TYPE_DISHES) {
            $detail->dishes_items = DishesItem::where('dishes_id', $detail->dishes_id)->get();
        }
        return Result::success($detail);
    }


    /**
     * 订单创建
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Exception
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
        if ($goods->status == Goods::STATUS_OFF) {
            throw new BaseResponseException('此商品已下架，请您选择其他商品');
        }

        $user = request()->get('current_user');

        $merchant = Merchant::findOrFail($goods->merchant_id);
        $merchant_oper = Oper::findOrFail($merchant->oper_id);
        $currentOperId = request()->get('current_oper_id');

        $order = new Order();
        $orderNo = Order::genOrderNo();
        $order->oper_id = $merchant->oper_id;
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
        $order->settlement_rate = $merchant->settlement_rate;
        $order->remark = request('remark', '');
        $order->pay_target_type = $merchant_oper->pay_to_platform ? Order::PAY_TARGET_TYPE_PLATFORM : Order::PAY_TARGET_TYPE_OPER;
        $order->save();

        if ($order->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM) { // 如果是支付到平台
            $currentOperId = 0;
            if ($currentOperId == 0) { // 在平台小程序下
                $isOperSelf = 2;
                $sdkConfig = $this->_payToPlatform($order);
            } else {
                $isOperSelf = 0;
                $sdkConfig = null;
            }
        } else {
            $isOperSelf = $merchant->oper_id === $currentOperId ? 1 : 0;
            if ($isOperSelf == 1) {
                $sdkConfig = $this->_wechatUnifyPayToOper($order);
            } else {
                $sdkConfig = null;
            }

        }

        return Result::success([
            'order_no' => $orderNo,
            'isOperSelf' => $isOperSelf,
            'sdk_config' => $sdkConfig,
        ]);
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
        $order->save();

        if ($order->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM) { // 如果是支付到平台
            if ($currentOperId == 0) { // 在平台小程序下
                // 调平台支付, 走融宝支付接口
                $isOperSelf = 2;
                $sdkConfig = $this->_payToPlatform($order);
            } else {
                $isOperSelf = 0;
                $sdkConfig = null;
            }
        } else {
            $isOperSelf = $merchant->oper_id === $currentOperId ? 1 : 0;
            if ($isOperSelf == 1) {
                $sdkConfig = $this->_wechatUnifyPayToOper($order);
            } else {
                $sdkConfig = null;
            }

        }


        return Result::success([
            'order_no' => $orderNo,
            'isOperSelf' => $isOperSelf,
            'sdk_config' => $sdkConfig,
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
     * 融宝支付参数获取
     * @param $order
     * @return mixed|string
     * @throws \Exception
     */
    private function reapalPrepay($order)
    {
        $param = [
            'title' => $order->goods_name,
            'body' => $order->goods_id,
            'order_no' => $order->order_no,
            'total_fee' => $order->pay_price,
            'merchantId' => $order->merchant_id,
            'store_name' => $order->merchant_name,
            'store_phone' => $merchant->contacter_phone ?? '15989438364',
            'open_id' => request()->get('current_open_id'),
        ];
        if (empty($param['body'])) {
            $param['body'] = $param['title'];
        }
        $reapal = new ReapalPay();
        $result = $reapal->prepay($param);

        if (empty($result['wxjsapi_str'])) {
            throw new BaseResponseException('微信支付失败');
        }
        return $result;
    }


    /**
     * 扫码付款
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Exception
     */
    public function scanQrcodePay()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);
        $price = request('price');
        if($price <= 0 || $price > 999999.99){
            throw new ParamInvalidException('价格不合法');
        }
        $user = request()->get('current_user');
        $currentOperId = request()->get('current_oper_id');
        $merchant = Merchant::findOrFail(request('merchant_id'));
        $merchant_oper = Oper::findOrFail($merchant->oper_id);

        $order = new Order();
        $orderNo = Order::genOrderNo();
        $order->order_no = $orderNo;
        $order->oper_id = $merchant->oper_id;
        $order->user_id = $user->id;
        $order->open_id = request()->get('current_open_id');
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
        $order->settlement_rate = $merchant->settlement_rate;
        $order->remark = request('remark', '');
        $order->pay_target_type = $merchant_oper->pay_to_platform ? Order::PAY_TARGET_TYPE_PLATFORM : Order::PAY_TARGET_TYPE_OPER;
        $order->save();

        if($order->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM){ // 如果是支付到平台
            if($currentOperId == 0){ // 在平台小程序下
                $isOperSelf = 2;
                $sdkConfig = $this->_payToPlatform($order);
            }else {
                $isOperSelf = 0;
                $sdkConfig = null;
            }
        }else {
            $isOperSelf = $merchant->oper_id === $currentOperId ? 1 : 0;
            if($isOperSelf == 1) {
                $sdkConfig = $this->_wechatUnifyPayToOper($order);
            }else {
                $sdkConfig = null;
            }

        }

        return Result::success([
            'order' => $order,
            'order_no' => $orderNo,
            'isOperSelf' => $isOperSelf,
            'sdk_config' => $sdkConfig,
        ]);
    }

    /**
     * 立即付款
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Exception
     */
    public function pay()
    {
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $orderNo = request('order_no');
        $order = Order::where('order_no', $orderNo)->firstOrFail();

        if ($order->status == Order::STATUS_PAID || $order->status == Order::STATUS_FINISHED) {
            throw new BaseResponseException('订单已支付，请重新发起订单');
        }
        if($order->status != Order::STATUS_UN_PAY){
            throw new BaseResponseException('订单状态异常');
        }

        if($order->oper_id !== request()->get('current_oper_id')){
            throw new BaseResponseException('该订单不是当前运营中心的订单');
        }

        // 检查订单中商品的状态，状态异常则关闭订单
        $this->_checkOrder($order);

        $currentOperId = request()->get('current_oper_id');
        if($order->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM){ // 如果是支付到平台
            if($currentOperId == 0){ // 在平台小程序下
                $isOperSelf = 2;
                $sdkConfig = $this->_payToPlatform($order);
            }else {
                $isOperSelf = 0;
                $sdkConfig = null;
            }
        }else {
            $merchant = MerchantService::getById($order->merchant_id);
            $isOperSelf = $merchant->oper_id === $currentOperId ? 1 : 0;
            if($isOperSelf == 1) {
                $sdkConfig = $this->_wechatUnifyPayToOper($order);
            }else {
                $sdkConfig = null;
            }

        }
        if($order)

        $sdkConfig = $this->_wechatUnifyPayToOper($order);

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


        if ($order->pay_target_type == $order::PAY_TARGET_TYPE_PLATFORM) {
            //支付到平台的用融宝支付退款
            $reapal = new ReapalPay();
            $result = $reapal->refund($order,$orderPay);
            exit;

        } else {
            // 发起微信支付退款
            $payApp = WechatService::getWechatPayAppForOper($order->oper_id);
            $result = $payApp->refund->byTransactionId($orderPay->transaction_no, $orderRefund->id, $orderPay->amount * 100, $orderPay->amount * 100, [
                'refund_desc' => '用户发起退款',
            ]);
            if($result['return_code'] === 'SUCCESS' && array_get($result, 'result_code') === 'SUCCESS'){
                // 微信退款成功
                $orderRefund->refund_id = $result['refund_id'];
                $orderRefund->status = 2;
                $orderRefund->save();

                $order->status = Order::STATUS_REFUNDED;
                $order->refund_price = $orderPay->amount;
                $order->refund_time = Carbon::now();
                $order->save();
                $this->decSellNumber($order);
                return Result::success($orderRefund);
            }else {
                Log::error('微信退款失败 :', [
                    'result' => $result,
                    'params' => [
                        '$orderPay->transaction_no' => $orderPay->transaction_no,
                        '$orderRefund->id' => $orderRefund->id,
                        '$orderPay->amount' => $orderPay->amount,
                        'refundAmount' => $orderPay->amount,
                        'refundOper' => $order->oper_id,
                    ]
                ]);
                throw new BaseResponseException('微信退款失败');
            }
        }

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
        $isOperSelf = 2;
        $sdkConfig = null;
        OrderService::paySuccess($order->order_no, 'pay_to_platform', $order->pay_price,Order::PAY_TYPE_WECHAT);

        // 调平台支付, 走融宝支付接口
        /*
        $isOperSelf = 1;
        $sdkConfig = null; // todo 走融宝支付接口

        $result = $this->reapalPrepay($order);
        $sdkConfig = json_decode($result['wxjsapi_str'],true);
        $sdkConfig['timestamp'] = $sdkConfig['timeStamp'];
        */

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

    /**
     * 检查订单中商品的状态，状态异常关闭订单
     * @param Order $order
     */
    private function _checkOrder(Order $order)
    {
        if ($order->type == Order::TYPE_GROUP_BUY){
            $goods = Goods::where('id', $order->goods_id)->first();
            if (empty($goods) || $goods->status == Goods::STATUS_OFF){
                $order->status = Order::STATUS_CLOSED;
                $order->save();
                throw new BaseResponseException('此商品已下架，请您选择其他商品');
            }
        } elseif ($order->type == Order::TYPE_DISHES){
            //判断商品上下架状态
            $dishesItems = DishesItem::where('dishes_id', $order->dishes_id)
                ->where('user_id', $order->user_id)
                ->get();
            foreach ($dishesItems as $item){
                $dishesGoods = DishesGoods::where('id', $item->dishes_goods_id)->first();
                if (empty($dishesGoods) || $dishesGoods->status == DishesGoods::STATUS_OFF){
                    $order->status = Order::STATUS_CLOSED;
                    $order->save();
                    throw new BaseResponseException('菜单已变更, 请刷新页面');
                }
            }
        }
    }
}