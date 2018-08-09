<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 12:38
 */

namespace App\Modules\Wechat;


use App\Exceptions\BaseResponseException;
use App\Exceptions\MiniprogramPageNotExistException;
use App\Modules\Oper\OperMiniprogram;
use App\ResultCode;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use Intervention\Image\Facades\Image;
use Intervention\Image\Gd\Font;

class WechatService
{
    /**
     * 获取微信小程序实例
     * @param $operId int
     * @return \EasyWeChat\MiniProgram\Application
     */
    public static function getWechatMiniAppForOper($operId)
    {
        $miniProgram = OperMiniprogram::where('oper_id', $operId)->first();
        if(empty($miniProgram)){
            throw new BaseResponseException('运营中心小程序配置不存在', ResultCode::MINIPROGRAM_CONFIG_NOT_EXIST);
        }
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

    /**
     * 获取微信支付的 EasyWechat App
     * @param $operId
     * @return \EasyWeChat\Payment\Application
     */
    public static function getWechatPayAppForOper($operId)
    {
        if($operId instanceof OperMiniprogram){
            $miniProgram = $operId;
        }else {
            $miniProgram = OperMiniprogram::where('oper_id', $operId)->firstOrFail();
        }

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

    /**
     * 生成小程序码
     * @param $operId
     * @param $sceneId
     * @param string $page
     * @param int $width
     * @param bool $getWithFilename
     * @return string
     */
    public static function genMiniprogramAppCode($operId, $sceneId, $page='pages/index/index', $width=375, $getWithFilename=false)
    {
        $app = WechatService::getWechatMiniAppForOper($operId);
        $response = $app->app_code->getUnlimit($sceneId, [
            'page' => $page,
            'width' => $width,
        ]);
        if($json = json_decode($response, 1)){
            if($json['errcode'] == 41030){
                throw new MiniprogramPageNotExistException();
            }
            throw new BaseResponseException('小程序码生成失败' . $response);
        }
        try {
            $filename = $response->save(storage_path('app/public/miniprogram/app_code'), "_{$sceneId}_{$width}");

            $path = storage_path('app/public/miniprogram/app_code/') . "_{$sceneId}_{$width}.jpg";

            $img = Image::make($path);
            $width = $img->width();

            $canvasWidth = intval(1.25 * $width);
            $canvasHeight = intval(1.35 * $width);
            $canvas = Image::canvas($canvasWidth, $canvasHeight, '#ffffff');
            $canvas->insert($path,  'top-left', intval(0.125 * $width), intval(0.125 * $width));
            $canvas->text(str_pad($sceneId, 8, "0", STR_PAD_LEFT), intval(0.5 * $canvasWidth), intval(1.275 * $width), function(Font $font) use ($width) {
                $size = intval(0.1 * $width);
                $font->file(public_path('../resources/fonts/MSYH.TTC'));
                $font->size($size);
                $font->align('center');
            });

            $canvas->save($path);

        } catch (InvalidArgumentException $e) {
            throw new BaseResponseException('小程序码生成失败');
        }
        if($getWithFilename){
            return $filename;
        }

        return asset('storage/miniprogram/app_code/' . $filename);
    }

    /**
     * @param $sceneId int|MiniprogramScene
     * @return mixed|string
     */
    public static function getMiniprogramAppCodeUrl($sceneId)
    {
        if($sceneId instanceof MiniprogramScene){
            $scene = $sceneId;
        }else {
            $scene = MiniprogramSceneService::getById($sceneId);
        }
        if(!empty($scene->qrcode_url)){
            return $scene->qrcode_url;
        }else {
            $url = self::genMiniprogramAppCode($scene->oper_id, $scene->id, $scene->page);
            $scene->qrcode_url = $url;
            $scene->save();
            return $url;
        }
    }

}