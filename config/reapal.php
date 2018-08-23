<?php

return [

    //测试商户ID
    'merchant_id' => '100000000000147',
    //测试商户邮箱
    'seller_email' => '850138237@qq.com',

    //商户私钥
    'merchantPrivateKey' => resource_path('reapal/cert/itrus001_pri.pem'),
    //融宝公钥
    'reapalPublicKey' => resource_path('reapal/cert/itrus001.pem'),

    'apiKey'   => env('REAPAL_APIKEY'),
    'dsfUrl'   => env('REAPAL_APIUrl'),
    'notify_url'   => env('REAPAL_NOTIFY_URL'),
    'charset'   => env('REAPAL_CHARSET'),
    'sign_type'   => env('REAPAL_SIGN_TYPE'),
    'version'   => env('REAPAL_VERSION'),
];