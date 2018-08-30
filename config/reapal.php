<?php

return [

    //测试商户ID
    'merchant_id' => env('MERCHANT_ID'),
    //测试商户邮箱
    'seller_email' => env('SELLER_EMAIL'),

    //商户私钥
    'merchantPrivateKey' => resource_path('reapal/cert/user-rsa.pem'),
    //融宝公钥
    'reapalPublicKey' => resource_path('reapal/cert/itrus001.pem'),

    'apiKey' => env('REAPAL_APIKEY'),
    'dsfUrl' => env('REAPAL_DSFURL'),
//    'notify_url' => env('REAPAL_NOTIFY_URL'),
    'charset' => 'utf-8',
    'dsf_sign_type' => 'MD5',
    'dsf_version' => '1.0',

    'api_url' => env('REAPAL_APIURL'),
    'api_version' => '3.1.3',
    'api_sign_type' => 'MD5',
];