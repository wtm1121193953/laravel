<?php

namespace App\Modules\Order;

use App\BaseModel;
use App\Exceptions\BaseResponseException;

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

    /**
     * 支付目标类型
     */
    const PAY_TARGET_TYPE_OPER = 1; // 支付给运营中心
    const PAY_TARGET_TYPE_PLATFORM = 2; // 支付给平台

    /**
     * 订单来源客户端app类型
     */
    const ORIGIN_APP_TYPE_ANDROID = 1; // 安卓
    const ORIGIN_APP_TYPE_IOS = 2; // iOS
    const ORIGIN_APP_TYPE_MINIPROGRAM = 3; // 小程序

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

    public static function getPayTypeText($payType)
    {
        return ['', '微信支付', '支付宝支付'][$payType];
    }

    public static function getPayTargetTypeText($payTargetType)
    {
        return ['', '运营中心', '平台'][$payTargetType];
    }

    public static function getOriginAppTypeText($originAppType)
    {
        return ['', '安卓', 'iOS', '小程序'][$originAppType];
    }

}
