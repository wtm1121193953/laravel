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
    'dsfUrl'   => env('REAPAL_DSFUrl'),
    'notify_url'   => env('REAPAL_NOTIFY_URL'),
    'charset'   => env('REAPAL_CHARSET'),
    'dsf_sign_type'   => env('REAPAL_DSF_SIGN_TYPE'),
    'dsf_version'   => env('REAPAL_DSF_VERSION'),

    'api_url'   => env('REAPAL_APIURL'),
    'api_version'   => env('REAPAL_API_VERSION'),
    'api_sign_type'   => env('REAPAL_API_SIGN_TYPE'),
];