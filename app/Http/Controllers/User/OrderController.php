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
use App\Modules\Merchant\MerchantDraft;
use App\Modules\Merchant\MerchantSettingService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderRefund;
use App\Modules\Setting\SettingService;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditRecord;
use App\Modules\Wechat\WechatService;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $user = request()->get('current_user');

        $merchantShareInMiniprogram = SettingService::getValueByKey('merchant_share_in_miniprogram');

        $currentOperId = request()->get('current_oper')->id;
        $data = Order::where('user_id', $user->id)
            ->where(function (Builder $query){
                $query->where('type', Order::TYPE_GROUP_BUY)
                    ->orWhere(function(Builder $query){
                        $query->where('type', Order::TYPE_SCAN_QRCODE_PAY)
                            ->whereIn('status', [4, 6, 7]);
                    })->orWhere('type', Order::TYPE_DISHES);
            })
            ->when($merchantShareInMiniprogram != 1, function(Builder $query) use ($currentOperId) {
                $query->where('oper_id', $currentOperId);
            })
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate();
        $data->each(function ($item) use ($currentOperId) {
            $item->items = OrderItem::where('order_id', $item->id)->get();
            // 判断商户是否是当前小程序关联运营中心下的商户
            $item->isOperSelf = $item->oper_id === $currentOperId ? 1 : 0;
            $item->goods_end_date = Goods::where('id', $item->goods_id)->value('end_date');
            $item->merchant_logo = Merchant::where('id', $item->merchant_id)->value('logo');
            $item->signboard_name = Merchant::where('id', $item->merchant_id)->value('signboard_name');
            if ($item->type == Order::TYPE_DISHES){
                $item->dishes_items = DishesItem::where('dishes_id', $item->dishes_id)->get();
            }
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
        // 只返回一个核销码
        $orderItem = OrderItem::where('order_id', $detail->id)->first();
        $detail->items = !empty($orderItem) ? [$orderItem] : [];
        $currentOperId = request()->get('current_oper')->id;
        // 判断商户是否是当前小程序关联运营中心下的商户
        $detail->isOperSelf = $detail->oper_id === $currentOperId ? 1 : 0;
        $detail->signboard_name = Merchant::where('id', $detail->merchant_id)->value('signboard_name');
        $creditRecord = UserCreditRecord::where('order_no', $detail->order_no)
            ->where('type', 1)
            ->first();
        if (!empty($creditRecord)){
            $detail->user_level = $creditRecord->user_level;
            $detail->user_level_text = User::getLevelText($creditRecord->user_level);
            $detail->credit = $creditRecord->credit;
        }
        if ($detail->type == Order::TYPE_DISHES){
            $detail->dishes_items = DishesItem::where('dishes_id', $detail->dishes_id)->get();
        }
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
        if ($goods->status == Goods::STATUS_OFF){
            throw new BaseResponseException('此商品已下架，请您选择其他商品');
        }

        $user = request()->get('current_user');

        $merchant = Merchant::findOrFail($goods->merchant_id);
        $oper = request()->get('current_oper');

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
        $order->remark = request('remark', '');
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
        $oper = request()->get('current_oper');

        if($userIdByDish!=$user->id){
            throw new ParamInvalidException('参数错误');
        }
        $result = MerchantSettingService::getValueByKey($dishes->merchant_id, 'dishes_enabled');
        if (!$result){
            throw new BaseResponseException('单品购买功能尚未开启！');
        }
        //判断商品上下架状态
        $dishesItems = DishesItem::where('dishes_id', $dishesId)
            ->where('user_id', $dishes->user_id)
            ->get();
        foreach ($dishesItems as $item){
            $dishesGoods = DishesGoods::findOrFail($item->dishes_goods_id);
            if ($dishesGoods->status == DishesGoods::STATUS_OFF){
                throw new BaseResponseException('菜单已变更, 请刷新页面');
            }
        }

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
        $order->remark = request('remark', '');
        $order->save();

        $isOperSelf = $merchant->oper_id === $oper->id ? 1 : 0;
        if($isOperSelf == 1) {
            $payApp = WechatService::getWechatPayAppForOper($merchant->oper_id);
            $data = [
                'body' =>  $merchant->name,
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

        return Result::success([
            'order_no' => $orderNo,
            'isOperSelf' => $isOperSelf,
            'sdk_config' => $sdkConfig,
        ]);
    }



    /**
     * 获取总价格
     */
    public function getTotalPrice(){
        $dishesId = request('dishes_id');
        $list = DishesItem::where('dishes_id',$dishesId)->get();
        $totalPrice = 0;
        foreach ($list as  $v){
                $totalPrice += ($v->dishes_goods_sale_price)*($v->number);
        }

       return  $totalPrice;

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
        ]);
        $price = request('price');
        if($price <= 0 || $price > 999999.99){
            throw new ParamInvalidException('价格不合法');
        }
        $user = request()->get('current_user');
        $merchant = Merchant::findOrFail(request('merchant_id'));

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
        $order->save();

        $isOperSelf = $merchant->oper_id === request()->get('current_oper')->id ? 1 : 0;
        if($isOperSelf == 1) {
            $sdkConfig = $this->_wechatUnifyPay($order);
        }else {
            $sdkConfig = null;
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

        if ($order->type == Order::TYPE_GROUP_BUY){
            $goods = Goods::findOrFail($order->goods_id);
            if ($goods->status == Goods::STATUS_OFF){
                throw new BaseResponseException('此商品已下架，请您选择其他商品');
            }
        }

        $sdkConfig = $this->_wechatUnifyPay($order);

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
                    'currentOper' => request()->get('current_oper')
                ]
            ]);
            throw new BaseResponseException('微信退款失败');
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