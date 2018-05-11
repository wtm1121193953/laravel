<?php

namespace App\Modules\Wechat;

use App\BaseModel;

class MiniprogramScene extends BaseModel
{
    // 订单支付页面
    const PAGE_ORDER_PAY = '';
    // 邀请注册页面
    const PAGE_INVITE_REGISTER = '';

    const TYPE_PAY_BRIDGE = 1;
    const TYPE_INVITE_CHANNEL = 2;
}
