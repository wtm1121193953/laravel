<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 17:11
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesGoods;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
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
use App\Modules\Payment\Payment;
use App\Modules\Payment\PaymentService;
use App\Modules\Setting\SettingService;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditRecord;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;
use App\Modules\Wechat\WechatService;
use App\Result;
use App\ResultCode;
use App\Support\Lbs;
use App\Support\Payment\PayBase;
use App\Support\Payment\WalletPay;
use App\Support\Payment\WechatPay;
use App\Support\Reapal\ReapalPay;
use App\Support\Utils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        $lng = request('lng');
        $lat = request('lat');
        $detail = Order::where('order_no', request('order_no'))->firstOrFail();
        // 只返回一个核销码
        $orderItem = OrderItem::where('order_id', $detail->id)->first();
        $detail->items = !empty($orderItem) ? [$orderItem] : [];
        $currentOperId = request()->get('current_oper_id');
        // 判断商户是否是当前小程序关联运营中心下的商户
        if($detail->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM){
            // 如果是需要支付到平台的订单
            if(!$currentOperId){ // 如果当前operId是0, 表示是在平台的小程序内
                $detail->isOperSelf = 1;
            }else {
                $detail->isOperSelf = 0;
            }
        }else {
            $detail->isOperSelf = $detail->oper_id === $currentOperId ? 1 : 0;
        }

        $detail->signboard_name = Merchant::where('id', $detail->merchant_id)->value('signboard_name');
        // 积分记录
        $creditRecord = UserCreditRecord::where('order_no', $detail->order_no)
            ->where('type', 1)
            ->first();
        if (!empty($creditRecord)) {
            $detail->user_level = $creditRecord->user_level;
            $detail->user_level_text = User::getLevelText($creditRecord->user_level);
            $detail->credit = $creditRecord->credit;
        }
        // 单品订单
        if ($detail->type == Order::TYPE_DISHES) {
            $detail->dishes_items = DishesItem::where('dishes_id', $detail->dishes_id)->get();
        }

        if($lng && $lat){
            $distance = Lbs::getDistanceOfMerchant($detail->merchant_id, request()->get('current_open_id'), floatval($lng), floatval($lat));
            // 格式化距离
            $detail->distance = Utils::getFormativeDistance($distance);
        }
        // 查看分润详情
        $feeSplittingRecord = FeeSplittingService::getToSelfFeeSplittingRecordByOrderId($detail->id);
        $detail->fee_splitting_amount = !empty($feeSplittingRecord) ? $feeSplittingRecord->amount : 0;

        // 贡献值
        $detail->consume_quota = floor($detail->pay_price);
        return Result::success($detail);
    }


    /**
     * 订单创建
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
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

        $payType = request('pay_type', Payment::ID_WECHAT);
        $user = request()->get('current_user');

        $merchant = MerchantService::getById($goods->merchant_id);
        $this->checkMerchant($merchant);

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
        $order->merchant_name = $merchant->signboard_name ?? '';
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
        $order->bizer_id = $merchant->bizer_id;
        $order->pay_type = $payType;
        $order->save();

        return $this->_returnOrder($order,$currentOperId,$merchant,$orderNo);
    }


    /**
     * 点菜订单创建
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function dishesBuy()
    {
        $this->validate(request(), [
            'dishes_id' => 'required|integer|min:1',
            'pay_type'  => 'integer'
        ]);
        $dishesId = request('dishes_id');
        $dishes = Dishes::findOrFail($dishesId);
        $userIdByDish = $dishes->user_id;
        $user = request()->get('current_user');
        $merchant = MerchantService::getById($dishes->merchant_id);
        $this->checkMerchant($merchant);

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
        $payType = request('pay_type',Payment::ID_WECHAT);

        $merchant_oper = Oper::findOrFail($merchant->oper_id);

        $order = new Order();
        $orderNo = Order::genOrderNo();
        $order->oper_id = $merchant->oper_id;
        $order->order_no = $orderNo;
        $order->user_id = $user->id;
        $order->open_id = request()->get('current_open_id');
        $order->user_name = $user->name ?? '';
        $order->type = Order::TYPE_DISHES;
        $order->notify_mobile = $user->mobile;
        $order->merchant_id = $merchant->id;
        $order->merchant_name = $merchant->signboard_name ?? '';
        $order->goods_name = $merchant->name ?? '';
        $order->dishes_id = $dishesId;
        $order->status = Order::STATUS_UN_PAY;
        $order->pay_price = $this->getTotalPrice();
        $order->settlement_rate = $merchant->settlement_rate;
        $order->remark = request('remark', '');
        $order->pay_target_type = $merchant_oper->pay_to_platform ? Order::PAY_TARGET_TYPE_PLATFORM : Order::PAY_TARGET_TYPE_OPER;
        $order->bizer_id = $merchant->bizer_id;
        $order->pay_type = $payType;
        $order->save();

        return $this->_returnOrder($order,$currentOperId,$merchant,$orderNo);
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
     * @throws \Exception
     */
    public function scanQrcodePay()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);
        $payTypeId = request()->get('pay_type_id');
        $payment = PaymentService::getDetailById($payTypeId);
        $price = request('price');
        if($price <= 0 || $price > 999999.99){
            throw new ParamInvalidException('价格不合法');
        }
        $user = request()->get('current_user');
        $currentOperId = request()->get('current_oper_id');
        $merchant = MerchantService::getById(request('merchant_id'));
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在！');
        }
        $this->checkMerchant($merchant);
        $merchant_oper = Oper::find($merchant->oper_id);
        if (empty($merchant_oper)) {
            throw new DataNotFoundException('该商户的运营中心不存在！');
        }
        $payType = request('pay_type', Payment::ID_WECHAT);
        $order = new Order();
        $orderNo = Order::genOrderNo();
        $order->order_no = $orderNo;
        $order->oper_id = $merchant->oper_id;
        $order->user_id = $user->id;
        $order->open_id = request()->get('current_open_id');
        $order->user_name = $user->name ?? '';
        $order->notify_mobile = request('notify_mobile') ?? $user->mobile;
        $order->merchant_id = $merchant->id;
        $order->merchant_name = $merchant->signboard_name ?? '';
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
        $order->bizer_id = $merchant->bizer_id;
        $order->pay_type = $payType;

        $order->save();
        return $this->_returnOrder($order,$currentOperId,$merchant,$orderNo);
    }

    /**
     * 立即付款
     * @throws \Exception
     */
    public function pay()
    {
        $this->validate(request(), [
            'order_no' => 'required',
            'pay_type' => 'integer'
        ]);
        $orderNo = request('order_no');
        $order = Order::where('order_no', $orderNo)->firstOrFail();
        $order->pay_type = request()->get('pay_type',Payment::ID_WECHAT);
        $order->save();
        $merchant = MerchantService::getById($order->merchant_id);
        $this->checkMerchant($merchant);

        if ($order->status == Order::STATUS_PAID || $order->status == Order::STATUS_FINISHED) {
            throw new BaseResponseException('订单已支付，请重新发起订单');
        }
        if($order->status != Order::STATUS_UN_PAY){
            throw new BaseResponseException('订单状态异常');
        }

        $currentOperId = request()->get('current_oper_id');
        if($currentOperId > 0 && $order->oper_id !== request()->get('current_oper_id')){
            throw new BaseResponseException('该订单不是当前运营中心的订单');
        }

        // 检查订单中商品的状态，状态异常则关闭订单
        $this->_checkOrder($order);

        return $this->_returnOrder($order,$currentOperId,$merchant,$orderNo);
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
        $order = OrderService::getInfoByOrderNo(request()->get('order_no'));
        $payment = PaymentService::getDetailById($order->pay_type);
        if($payment->type==Payment::TYPE_WECHAT){
            // 如果为微信支付,则返回支付参数
            $m = new WechatPay();
            $res =  $m->refund($order);
        }else{
            $paymentClassName = '\\App\\Support\\Payment\\'.$payment->class_name;
            if(!class_exists($paymentClassName)){
                throw new BaseResponseException('无法使用该退款方式');
            }
            $paymentClass = new $paymentClassName();
            $res =  $paymentClass->refund($order,request()->get('current_user'));
        }
        // 还原库存
        $this->decSellNumber($order);
        return $res;
    }


    /**
     * 订单支付到平台, 返回微信支付参数
     * @param $order
     * @return null|array
     * @throws \Exception
     */
    private function _payToPlatform($order)
    {
        $payment = PaymentService::getDetailById($order->pay_type);
        if($payment->type==Payment::TYPE_WECHAT){
            // 如果为微信支付,则返回支付参数
            $payApp = WechatService::getWechatPayAppForPlatform();
            return $this->_payByWechat($order,$payApp);
        }
        $paymentClassName = '\\App\\Support\\Payment'.$payment->class_name;
        if(!class_exists($paymentClassName)){
            throw new BaseResponseException('无法使用该支付方式');
        }
        $paymentClass = new $paymentClassName();
        return $paymentClass->buy($order);
    }

    /**
     * 平台支付退款
     */
    private function _refundFromPlatform($order,$orderPay, $orderRefund)
    {

        return ['refund_id' => 'mock refund id'];
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
     * 处理订单返回
     * @param $order
     * @param $currentOperId
     * @param $merchant
     * @param $orderNo
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    private function _returnOrder($order,$currentOperId,$merchant,$orderNo){
        $sdkConfig = null;
        $isOperSelf = 0;
        $data = null;

        $payment = PaymentService::getDetailById($order->pay_type);
        if($payment->type==Payment::TYPE_WECHAT){
            // 如果为微信支付 走以下逻辑
            if($order->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM){
                if($currentOperId == 0){ // 在平台小程序下
                    $isOperSelf = 1;
                    // 如果为微信支付,则返回支付参数
                    $payApp = WechatService::getWechatPayAppForPlatform();
                    $sdkConfig = $this->_payByWechat($order,$payApp);
                }
            }else{
                $isOperSelf = $merchant->oper_id === $currentOperId ? 1 : 0;
                if($isOperSelf == 1) {
                    $payApp = WechatService::getWechatPayAppForOper($order->oper_id);
                    $sdkConfig = $this->_payByWechat($order,$payApp);
                }
            }
        }else{
            $isOperSelf = 1;
            $paymentClassName = '\\App\\Support\\Payment\\'.$payment->class_name;
            if(!class_exists($paymentClassName)){
                throw new BaseResponseException('无法使用该支付方式');
            }
            $paymentClass = new $paymentClassName();
            try{
                $data =  $paymentClass->buy($order);
            }catch (\Exception $e){
                if($e instanceof ValidationException){
                    $message = implode(',',array_map(function(&$value){
                        return implode('|', $value);
                    }, $e->errors()));;
                }else{
                    $message = $e->getMessage();
                }
                return Result::error(
                    ResultCode::PARAMS_INVALID,
                    $message,[
                    'order_no' => $orderNo,
                    'isOperSelf' => $isOperSelf,
                    'sdk_config' => $sdkConfig,
                    'pay_type'  =>  $order->pay_type,
                    'order' =>  $order,
                    'anther_pay'  =>  $data
                ]);
            }

        }

        return Result::success([
            'order_no' => $orderNo,
            'isOperSelf' => $isOperSelf,
            'sdk_config' => $sdkConfig,
            'pay_type'  =>  $order->pay_type,
            'order' =>  $order,
            'anther_pay'  =>  $data
        ]);
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


    /**
     * 通过微信支付
     * @param $order
     * @param $payApp
     * @return array|null
     */
    private function _payByWechat($order,$payApp){
        $sdkConfig = null;
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
    }

    /**
     * 下单检查商户状态
     * @param Merchant $merchant
     * @return bool
     */
    private function checkMerchant(Merchant $merchant)
    {

        if ($merchant->audit_status != Merchant::AUDIT_STATUS_SUCCESS && $merchant->audit_status != Merchant::AUDIT_STATUS_RESUBMIT) {
            throw new BaseResponseException('商家异常，请联系商家');
        }
        if($merchant->status == Merchant::STATUS_OFF){
            throw new BaseResponseException('商家异常，请联系商家');
        }

        return true;
    }


}