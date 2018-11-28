<?php
/**
 * 钱包支付
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/29/029
 * Time: 14:14
 */
namespace App\Support\Payment;

use App\Exceptions\BaseResponseException;
use App\Exceptions\NoPermissionException;
use App\Modules\CsStatistics\CsStatisticsMerchantOrderService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderRefund;
use App\Modules\Order\OrderService;
use App\Modules\Payment\Payment;
use App\Modules\Platform\PlatformTradeRecord;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;
use App\Result;
use App\Support\Payment\PayBase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class WalletPay extends PayBase
{
    /**
     * @param $user
     * @param $order
     * @throws \Exception
     */
    public function buy($user, $order)
    {
        // 判断密码的有效性
        /*request()->validate([
            'temp_token' => 'required'
        ]);*/
        $inputToken = request()->get('temp_token');
        $tempToken = Cache::get('user_pay_password_modify_temp_token_' . $user->id);
        if(empty($tempToken)){
            throw new NoPermissionException('您的验证信息已超时, 请返回重新验证');
        }
        if($tempToken != $inputToken){
            throw new NoPermissionException('验证信息无效');
        }
        // 删除有效时间，避免重复提交
        Cache::forget('user_pay_password_modify_temp_token_' . $user->id);

        // 获取钱包信息
        $wallet = WalletService::getWalletInfoByOriginInfo($user->id, Wallet::ORIGIN_TYPE_USER);
        // 新增流水
        $walletBill = WalletService::minusBalance($wallet,$order->pay_price,WalletBill::TYPE_PLATFORM_SHOPPING,$order->id);
        // 处理支付成功逻辑
        OrderService::paySuccess($order->order_no,$walletBill->bill_no,$order->pay_price,$order->pay_type);
    }

    public function doNotify()
    {
        // TODO: Implement doNotify() method.
    }

    public function refund($order,$user)
    {
        if(($order->status != Order::STATUS_PAID) || ($order->status != Order::STATUS_UNDELIVERED)){
            throw new BaseResponseException('订单状态不允许退款');
        }
        if ($order->pay_type != Payment::ID_WALLET) {
            throw new BaseResponseException('不是钱包支付的订单');
        }

        // 查询支付记录
        $orderPay = OrderPay::where('order_id', $order->id)->firstOrFail();
        // 生成退款单
        $orderRefund = new OrderRefund();
        $orderRefund->order_id = $order->id;
        $orderRefund->order_no = $order->order_no;
        $orderRefund->amount = $orderPay->amount;
        $orderRefund->save();
        $wallet = WalletService::getWalletInfo($user);
        WalletService::addBalance($wallet,$order->pay_price,WalletBill::TYPE_PLATFORM_REFUND,$orderRefund->id);
        // 修改order表状态
        $order->status = Order::STATUS_REFUNDED;
        $order->refund_time = Carbon::now();
        $order->refund_price = $orderPay->amount;
        $order->save();

        $platform_trade_record = new PlatformTradeRecord();
        $platform_trade_record->type = PlatformTradeRecord::TYPE_REFUND;
        $platform_trade_record->pay_id = 1;
        $platform_trade_record->trade_amount = $orderPay->amount;
        $platform_trade_record->trade_time = $order->refund_time;
        $platform_trade_record->trade_no = ($orderRefund->refund_id) ? $orderRefund->refund_id:'';
        $platform_trade_record->order_no = $order->order_no;
        $platform_trade_record->oper_id = $order->oper_id;
        $platform_trade_record->merchant_id = $order->merchant_id;
        $platform_trade_record->user_id = $order->user_id;
        $platform_trade_record->remark = '';
        $platform_trade_record->save();

        //如果是超市商户，更新商户当月销量
        if ($order->type == Order::TYPE_SUPERMARKET) {
            CsStatisticsMerchantOrderService::minusCsMerchantOrderNumberToday($order->merchant_id);
        }
        return Result::success($orderRefund);

    }

}