<?php

namespace App\Modules\Payment;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * @package App\Modules\Payment
 * @property string name
 * @property string class_name
 * @property string logo_url
 * @property tinyInteger type
 * @property tinyInteger status
 * @property tinyInteger on_pc
 * @property tinyInteger on_miniprogram
 * @property tinyInteger on_app
 * @property string view_name
 * @property text configs
 */
class Payment extends Model
{
    //
    const TYPE_WECHAT = 1;
    const TYPE_ALIPAY = 2;
    const TYPE_WALLET = 0;

    const PC_ON = 1;
    const PC_OFF = 0;

    const MINI_PROGRAM_ON = 1;
    const MINI_PROGRAM_OFF= 0;

    const APP_ON = 1;
    const APP_OFF= 0;

    const STATUS_ON = 1;
    const STATUS_OFF= 0;

    public static function getAllType()
    {
        return [self::TYPE_WECHAT=>'微信',self::TYPE_ALIPAY=>'支付宝',self::TYPE_WALLET=>'钱包'];
    }
}
