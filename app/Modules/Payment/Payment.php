<?php

namespace App\Modules\Payment;

use App\BaseModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * @package App\Modules\Payment
 * @property string name
 * @property string class_name
 * @property string logo_url
 * @property int type
 * @property int status
 * @property int on_pc
 * @property int on_miniprogram
 * @property int on_app
 * @property string view_name
 * @property string configs
 */
class Payment extends BaseModel
{
    //
    const TYPE_WECHAT = 1;
    const TYPE_ALIPAY = 2;
    const TYPE_WALLET = 4;

    const ID_WECHAT = 1;
    const ID_ALIPAY = 2;
    const ID_WALLET = 4;

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
        return [self::TYPE_WECHAT=>'微信',self::TYPE_ALIPAY=>'支付宝',self::TYPE_WALLET=>'钱包余额'];
    }
}
