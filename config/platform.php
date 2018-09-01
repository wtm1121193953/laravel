<?php

return [
    'miniprogram' => [
        'app_id' => env('APP_ENV') == 'production' ? 'wxdc74ddcdb8389b98' : 'wx8d0f5e945df699c2',
        'app_secret' => env('APP_ENV') == 'production' ? '5e2648fc79ccd467f6abadb3347d813e' : '8f5fed3b1edfa44d409416db60f22bfb',
    ],
    'wechat_pay' => [
        'mch_id' => '',
        'key' => '',
    ]
];