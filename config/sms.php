<?php

return [
//    'verify_code_template' => '您的手机验证码是：%s，若非本人操作，请忽略！',

    'template' => [
        'verify_code' => '您的手机验证码是：{verifyCode}，若非本人操作，请忽略！',
        'group_buy' => '订单号{orderNo}：{name}已下单成功，份数：{number}，使用截止日期：{endDate}，请凭券码{verifyCode}到商家进行消费，感谢您的使用。',
        'dishes_buy' => '订单号{orderNo}：{name}等{number}份商品已下单成功，请及时到商家进行消费，感谢您的使用。'
    ]
];