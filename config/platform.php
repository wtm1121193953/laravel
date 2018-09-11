<?php

return [
    'miniprogram' => [
        'app_id' => env('APP_ENV') == 'production' ? 'wx0e8e29c73ec2ae88' : 'wx8d0f5e945df699c2',
        'app_secret' => env('APP_ENV') == 'production' ? 'e0f7d6492e089f45671b7a5408962315' : '6a8d93afba52c4b88e12d46c70bec1bf',
    ],
    'wechat_pay' => [
        'mch_id' => '1513745891',
        'key' => '57k3434rgtk0g4w4ey45guerdudkjty3',
        'cert_path' => storage_path('app/wxPayCert/platform/apiclient_cert.pem'),
        'key_path' => storage_path('app/wxPayCert/platform/apiclient_key.pem'),
    ]
];