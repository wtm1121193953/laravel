<?php

namespace App\Modules\Order;

use App\BaseModel;
use App\Exceptions\BaseResponseException;
use App\Modules\Oper\Oper;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use Carbon\Carbon;

/**
 * Class Order
 * @package App\Modules\Order
 *
 * @property number oper_id
 * @property number user_id
 * @property string open_id
 * @property string order_no
 * @property string user_name
 * @property string notify_mobile
 * @property number merchant_id
 * @property string merchant_name
 * @property number type
 * @property number goods_id
 * @property number dishes_id
 * @property string goods_name
 * @property string goods_pic
 * @property string goods_thumb_url
 * @property number price
 * @property number buy_number
 * @property number status
 * @property number  pay_type
 * @property number pay_price
 * @property Carbon pay_time
 * @property number pay_target_type
 * @property float settlement_rate
 * @property number refund_price
 * @property Carbon refund_time
 * @property Carbon finish_time
 * @property number settlement_status
 * @property number origin_app_type
 * @property string remark
 * @property number settlement_id
 * @property number settlement_real_amount
 * @property number settlement_charge_amount
 * @property integer splitting_status
 * @property Carbon splitting_time
 * @property integer bizer_id
 * @property float bizer_divide
 */

class Order extends BaseModel
{
    /**
     * 订单类型
     */
    const TYPE_GROUP_BUY = 1; // 团购订单
    const TYPE_SCAN_QRCODE_PAY = 2; // 扫码支付订单, 即直接输入金额支付
    const TYPE_DISHES = 3; // 点菜订单

    /**
     * 支付类型
     */
    const PAY_TYPE_WECHAT = 1; // 微信支付
    const PAY_TYPE_ALIPAY = 2; // 支付宝支付
    const PAY_TYPE_REAPAL = 3; // 融宝支付

    /**
     * 支付目标类型
     */
    const PAY_TARGET_TYPE_OPER = 1; // 支付给运营中心
    const PAY_TARGET_TYPE_PLATFORM = 2; // 支付给平台
    const PAY_TARGET_TYPE_PLATFORM_3 = 3; // 支付给平台且平台参与分成

    /**
     * 订单来源客户端app类型
     */
    const ORIGIN_APP_TYPE_ANDROID = 1; // 安卓
    const ORIGIN_APP_TYPE_IOS = 2; // iOS
    const ORIGIN_APP_TYPE_MINIPROGRAM = 3; // 小程序

     /**
     * 结算状态
     * Author:Jerry
     * Date:180824
     */
    const SETTLEMENT_STATUS_NO = 1;         // 未结算
    const SETTLEMENT_STATUS_FINISHED = 2;   // 已结算

    /**
     * 订单状态
     */
    const STATUS_UN_PAY = 1; // 未支付
    const STATUS_CANCEL = 2; // 已取消
    const STATUS_CLOSED = 3; // 已关闭
    const STATUS_PAID = 4; // 已支付
    const STATUS_REFUNDING = 5; // 退款中
    const STATUS_REFUNDED = 6; // 已退款
    const STATUS_FINISHED = 7; // 已完成

    /**
     * 分润状态
     */
    const SPLITTING_STATUS_YES = 2; //已分润
    const SPLITTING_STATUS_NO = 1; //未分润

    /**
     * 生成订单号, 订单号规则: O{年月日时分秒}{6位随机数}
     * @param int $retry
     * @return string
     */
    public static function genOrderNo($retry = 100)
    {
        if($retry == 0){
            throw new BaseResponseException('订单号生成已超过最大重试次数');
        }
        $orderNo = 'O' . date('YmdHis') . rand(100000, 999999);
        if(Order::where('order_no', $orderNo)->first()){
            $orderNo = self::genOrderNo(--$retry);
        }
        return $orderNo;
    }

    public static function getTypeText($type)
    {
        return ['', '团购', '买单','单品'][$type];
    }

    public static function getStatusText($status)
    {
        return ['', '未支付', '已取消', '已关闭[超时自动关闭]', '已支付', '退款中[保留状态]', '已退款', '已完成[不可退款]'][$status];
    }

    public static function getPayTypeText($payType){
        if($payType ==1){
            $payTypeText = '微信';
        }elseif($payType == 2){
            $payTypeText = '支付宝';
        }elseif($payType == 3){
            $payTypeText = '融宝';
        }else{
            $payTypeText = '未知('.$payType.')';
        }

        return $payTypeText;
    }

    public static function getPayTargetTypeText($payTargetType)
    {
        return ['', '运营中心', '平台'][$payTargetType];
    }

    public static function getOriginAppTypeText($originAppType)
    {
        return ['', '安卓', 'iOS', '小程序'][$originAppType];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|WalletConsumeQuotaRecord
     */
    public function consumeQuotaRecords()
    {
        return $this->hasMany(WalletConsumeQuotaRecord::class);
    }

    /*protected $dispatchesEvents = [
        'updated' => \App\Events\OrdersUpdatedEvent::class,
    ];*/

    public function oper()
    {
        return $this->belongsTo(Oper::class);
    }
}
