<?php

namespace App\Modules\Oper;

use App\BaseModel;
use Illuminate\Support\Facades\Storage;

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
