<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 15:59
 */

namespace App\Modules\Oper;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\ResultCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class OperMiniprogramService extends BaseService
{

    /**
     * 根据小程序配置ID获取小程序配置
     * @param $id
     * @return OperMiniprogram
     */
    public static function getById($id)
    {
        return OperMiniprogram::where('id', $id)->first();
    }

    /**
     * 根据运营中心ID获取运营中心的小程序信息
     * @param $operId
     * @return OperMiniprogram
     */
    public static function getByOperId($operId)
    {
        return OperMiniprogram::where('oper_id', $operId)->first();
    }

    /**
     * 根据运营中心appid获取运营中心的小程序信息
     * @param $appid
     * @return OperMiniprogram
     */
    public static function getByAppid($appid)
    {
        return OperMiniprogram::where('appid', $appid)->first();
    }

    /**
     * 添加运营中心的小程序
     * @param $operId int 运营中心ID
     * @param $miniprogramName string 小程序名称
     * @param $appid string 小程序appid
     * @param $secret string 小程序 app secret
     * @param $mchId string 小程序关联的支付商户 mch_id
     * @param $payKey string 支付商户密钥
     * @param $verifyFilePath string 小程序业务验证文件地址(需先上传文件, 再讲返回的链接提交到该方法中)
     * @return OperMiniprogram
     */
    public static function add($operId, $miniprogramName, $appid, $secret, $mchId, $payKey, $verifyFilePath)
    {

        $miniprogram = self::getByOperId($operId);
        if(!empty($miniprogram)){
            throw new BaseResponseException('该运营中心已添加小程序配置,请勿重复添加', ResultCode::MINIPROGRAM_CONFIG_ALREADY_EXIST);
        }

        $miniprogram = new OperMiniprogram();
        $miniprogram->oper_id = $operId;
        $miniprogram->name = $miniprogramName;
        $miniprogram->appid = $appid;
        $miniprogram->secret = $secret;
        $miniprogram->mch_id = $mchId;
        $miniprogram->key = $payKey;
        $miniprogram->verify_file_path = $verifyFilePath;

        $miniprogram->save();

        return $miniprogram;
    }

    /**
     * 编辑小程序配置
     * @param $operId int 运营中心ID
     * @param $miniprogramName string 小程序名称
     * @param $appid string 小程序appid
     * @param $secret string 小程序 app secret
     * @param $mchId string 小程序关联的支付商户 mch_id
     * @param $payKey string 支付商户密钥
     * @param $verifyFilePath string 小程序业务验证文件地址(需先上传文件, 再讲返回的链接提交到该方法中)
     * @return OperMiniprogram
     */
    public static function edit($operId, $miniprogramName, $appid, $secret, $mchId, $payKey, $verifyFilePath)
    {
        $miniprogram = self::getByOperId($operId);
        if(empty($miniprogram)){
            throw new BaseResponseException('该运营中心尚未添加小程序, 请先添加小程序配置', ResultCode::MINIPROGRAM_CONFIG_NOT_EXIST);
        }

        $miniprogram->oper_id = $operId;
        $miniprogram->name = $miniprogramName;
        $miniprogram->appid = $appid;
        $miniprogram->secret = $secret;
        $miniprogram->mch_id = $mchId;
        $miniprogram->key = $payKey;
        $miniprogram->verify_file_path = $verifyFilePath;

        $miniprogram->save();

        return $miniprogram;
    }

    /**
     * 上传微信支付证书
     * @param int $miniprogramId
     * @param UploadedFile $file
     * @return OperMiniprogram
     */
    public static function uploadPayCert($miniprogramId, $file)
    {
        $miniprogram = self::getById($miniprogramId);
        if(empty($miniprogram)){
            throw new BaseResponseException('该运营中心尚未添加小程序, 请先添加小程序配置', ResultCode::MINIPROGRAM_CONFIG_NOT_EXIST);
        }

        $path = $file->storeAs(OperMiniprogram::getCertDir($miniprogram->mch_id), 'cert.zip');

        // 解压缩文件
        $zip = new \ZipArchive();
        if($zip->open(Storage::path($path)) !== true){
            throw new BaseResponseException('解压缩文件错误');
        }
        $zip->extractTo(OperMiniprogram::getAbsoluteCertDir($miniprogram->mch_id));
        $zip->close();

        // 修改miniprogram中的证书路径
        $miniprogram->cert_zip_path = $path;
        $miniprogram->save();

        return $miniprogram;
    }

    /**
     * 上传小程序服务器验证文件
     * @param UploadedFile $file
     * @return string 返回文件访问的路径
     */
    public static function uploadVerifyFile($file)
    {

        if($file->getClientOriginalExtension() !== 'txt'){
            throw new BaseResponseException('必须为txt格式文件');
        }

        $filename = $file->getClientOriginalName();
        $content = file_get_contents($file->path());

        file_put_contents(public_path($filename), $content);
        $path = '/' . $filename;

        return $path;
    }
}