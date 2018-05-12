<?php

namespace App\Modules\Wechat;

use App\BaseModel;

class MiniprogramScene extends BaseModel
{
    // 订单支付页面(订单支付页面的路径暂时由小程序传参过来)
    const PAGE_ORDER_PAY = '';
    // 邀请注册页面
    const PAGE_INVITE_REGISTER = 'pages/login/index';

    const TYPE_PAY_BRIDGE = 1;
    const TYPE_INVITE_CHANNEL = 2;
}
