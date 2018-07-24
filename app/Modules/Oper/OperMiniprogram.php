<?php

namespace App\Modules\Oper;

use App\BaseModel;
use Illuminate\Support\Facades\Storage;

/**
 * Class OperMiniprogram
 * @package App\Modules\Oper
 *
 * @property number oper_id
 * @property string name
 * @property string appid
 * @property string secret
 * @property string verify_file_path
 * @property string mch_id
 * @property string key
 * @property string cert_zip_path
 */

class OperMiniprogram extends BaseModel
{
    //

    public static function getCertDir($mchId)
    {
        return 'wxPayCert/' . $mchId;
    }

    public static function getAbsoluteCertDir($mchId)
    {
        return Storage::path(self::getCertDir($mchId));
    }

    public static function getAbsoluteCertPemFilePath($mchId)
    {
        return self::getAbsoluteCertDir($mchId) . '/apiclient_cert.pem';
    }

    public static function getAbsoluteCertKeyFilePath($mchId)
    {
        return self::getAbsoluteCertDir($mchId) . '/apiclient_key.pem';
    }
}
