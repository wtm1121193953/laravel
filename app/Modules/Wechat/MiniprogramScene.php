<?php

namespace App\Modules\Wechat;

use App\BaseModel;

/**
 * Class MiniprogramScene
 * @package App\Modules\Wechat
 *
 * @property int    oper_id
 * @property int    merchant_id
 * @property int    invite_channel_id
 * @property int    type
 * @property string page
 * @property string payload
 * @property string qrcode_url
 *
 */
class MiniprogramScene extends BaseModel
{
    // 订单支付页面(订单支付页面的路径暂时由小程序传参过来)
    const PAGE_ORDER_PAY = 'pages/order/info';
    // 邀请注册页面
    const PAGE_INVITE_REGISTER = 'pages/login/index';
    // 扫码支付页面
    const PAGE_PAY_SCAN = 'pages/order/addOfPrice';

    const TYPE_PAY_BRIDGE = 1; // 小程序间支付跳转
    const TYPE_INVITE_CHANNEL = 2; // 邀请注册渠道
    const TYPE_PAY_SCAN = 3; // 扫码支付
}
