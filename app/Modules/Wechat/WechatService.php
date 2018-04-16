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
    /**
     * 获取微信小程序实例
     * @param $operId int
     * @return \EasyWeChat\MiniProgram\Application
     */
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

    public static function getWechatPayAppForOper($operId)
    {
        $miniProgram = OperMiniprogram::where('oper_id', $operId)->firstOrFail();

        $config = [
            // 必要配置
            'app_id' => $miniProgram->appid,
            'mch_id'             => $miniProgram->mch_id,
            'key'                => $miniProgram->key,   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => OperMiniprogram::getAbsoluteCertPemFilePath($miniProgram->mch_id), // XXX: 绝对路径！！！！
            'key_path'           => OperMiniprogram::getAbsoluteCertKeyFilePath($miniProgram->mch_id),      // XXX: 绝对路径！！！！

            'notify_url' => request()->getSchemeAndHttpHost() . '/api/pay/notify',     // 你也可以在下单时单独设置来想覆盖它
        ];

        return Factory::payment($config);
    }
}