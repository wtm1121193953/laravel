<?php

return [
    // 平台小程序配置
    'miniprogram' => [
        'app_id' => env('APP_ENV') == 'production' ? 'wx0e8e29c73ec2ae88' : 'wx8d0f5e945df699c2',
        'app_secret' => env('APP_ENV') == 'production' ? 'e0f7d6492e089f45671b7a5408962315' : '6a8d93afba52c4b88e12d46c70bec1bf',
    ],
    // 微信开放平台app应用配置
    'wechat_open' => [
        'app_id' => 'wxd1a8a54d81ebf5fa',
        'app_secret' => 'e0f7d6492e089f45671b7a5408962315',
    ],
    // 平台微信支付配置
    'wechat_pay' => [
        'miniprogram' => [
            'mch_id' => '1513745891',
            'key' => '57k3434rgtk0g4w4ey45guerdudkjty3',
            'cert_path' => storage_path('app/wxPayCert/platform/miniprogram/apiclient_cert.pem'),
            'key_path' => storage_path('app/wxPayCert/platform/miniprogram/apiclient_key.pem'),
        ],
        'app' => [
            'mch_id' => '1514435661',
            'key' => '3kDjtZLtK2vhgB0FvG5OQuCk5lWIB7e6',
            'cert_path' => storage_path('app/wxPayCert/app/1514435661_20180914_cert.pem'),
            'key_path' => storage_path('app/wxPayCert/app/1514435661_20180914_key.pem'),
        ]
    ],
    // 支付宝支付配置
];