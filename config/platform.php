<?php

return [
    // 平台小程序配置
    'miniprogram' => [
        // 新小程序
        'app_id' => env('APP_ENV') == 'production' ? 'wx3e070e382ddde646' : 'wx34b4a02220e854d8',
        'app_secret' => env('APP_ENV') == 'production' ? 'ed8dfc08cbd0f5d630e7a7caa7ec38ce' : '50e65d209b61529a1c58193073ebaa34',

        // 旧的小程序, 需要做兼容, 旧小程序也可访问, 支付时跳转到新小程序
        'old' => [
            'app_id' => env('APP_ENV') == 'production' ? 'wx0e8e29c73ec2ae88' : 'wx8d0f5e945df699c2',
            'app_secret' => env('APP_ENV') == 'production' ? 'e0f7d6492e089f45671b7a5408962315' : '6a8d93afba52c4b88e12d46c70bec1bf',
        ],
    ],
    // 微信开放平台app应用配置
    'wechat_open' => [
        'app_id' => 'wxd1a8a54d81ebf5fa',
        'app_secret' => 'e0f7d6492e089f45671b7a5408962315',
    ],
    // 平台微信支付配置
    'wechat_pay' => [
        'miniprogram' => [
//            'mch_id' => '1513745891',
//            'key' => '57k3434rgtk0g4w4ey45guerdudkjty3',
//            'cert_path' => storage_path('app/wxPayCert/platform/miniprogram/apiclient_cert.pem'),
//            'key_path' => storage_path('app/wxPayCert/platform/miniprogram/apiclient_key.pem'),
            // 新微信支付: 大千生活科技主体的支付
            'mch_id' => '1516321591',
            'key' => 'nNfOgMNbck42f8N6QnhvIuVWBgoaHb7L',
            'cert_path' => storage_path('app/wxPayCert/platform/miniprogram/new/apiclient_cert.pem'),
            'key_path' => storage_path('app/wxPayCert/platform/miniprogram/new/apiclient_key.pem'),
        ],
        'app' => [
            'mch_id' => '1514435661',
            'key' => '3kDjtZLtK2vhgB0FvG5OQuCk5lWIB7e6',
            'cert_path' => storage_path('app/wxPayCert/platform/app/1514435661_20180914_cert.pem'),
            'key_path' => storage_path('app/wxPayCert/platform/app/1514435661_20180914_key.pem'),
        ]
    ],
    // 支付宝支付配置
];