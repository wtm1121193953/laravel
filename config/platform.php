<?php

return [
    // 平台小程序配置
    'miniprogram' => [
        // 新小程序
        'gh_id' => env('APP_ENV') == 'production' ? 'gh_01a1f482ac66' : 'gh_b3dec410dad9', // 原始ID
        'app_id' => env('APP_ENV') == 'production' ? 'wx3e070e382ddde646' : 'wx34b4a02220e854d8',
        'app_secret' => env('APP_ENV') == 'production' ? 'ed8dfc08cbd0f5d630e7a7caa7ec38ce' : '50e65d209b61529a1c58193073ebaa34',

        // 旧的小程序, 需要做兼容, 旧小程序也可访问, 支付时跳转到新小程序
        'old' => [
            'app_id' => env('APP_ENV') == 'production' ? 'wx0e8e29c73ec2ae88' : 'wx8d0f5e945df699c2',
            'app_secret' => env('APP_ENV') == 'production' ? 'e0f7d6492e089f45671b7a5408962315' : 'a92b4d5ef424b7d2f8423e3df671e603',
        ],
    ],
    // 微信开放平台app应用配置
    'wechat_open' => [
        'app_id' => 'wxece68e67f4f57bb7',
        'app_secret' => '6f783d87b7471e8353f14d9e9a7e04b6',
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
            'mch_id' => '1516318411',
            'key' => 'nNfOgerbck15f8N6QnhvIuVWBgoaHb8e',
            'cert_path' => storage_path('app/wxPayCert/platform/app/1516318411_20181102_cert.pem'),
            'key_path' => storage_path('app/wxPayCert/platform/app/1516318411_20181102_key.pem'),
        ]
    ],
    // 支付宝支付配置
];