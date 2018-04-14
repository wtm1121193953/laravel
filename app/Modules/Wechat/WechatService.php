<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 12:38
 */

namespace App\Modules\Wechat;


use App\Modules\Oper\OperMiniprogram;
use EasyWeChat\Factory;

class WechatService
{
    public static function getWechatMiniAppForOper($operId)
    {
        $miniProgram = OperMiniprogram::where('oper_id', $operId)->firstOrFail();
        $config = [
            'app_id' => $miniProgram->appid,
            'secret' => $miniProgram->secret,

            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => storage_path().'/logs/wechat.log',
            ],
        ];

        return Factory::miniProgram($config);
    }
}