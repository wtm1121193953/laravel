<?php

return [
    'miniprogram' => [
        'app_id' => env('APP_ENV') == 'production' ? 'wxdc74ddcdb8389b98' : 'wx8d0f5e945df699c2',
        'app_secret' => env('APP_ENV') == 'production' ? '5e2648fc79ccd467f6abadb3347d813e' : '6a8d93afba52c4b88e12d46c70bec1bf',
    ],
    'wechat_pay' => [
        'mch_id' => '1513745891',
        'key' => '57k3434rgtk0g4w4ey45guerdudkjty3',
        'cert_path' => storage_path('app/wxPayCert/platform/apiclient_cert.pem'),
        'key_path' => storage_path('app/wxPayCert/platform/apiclient_key.pem'),
    ]
];