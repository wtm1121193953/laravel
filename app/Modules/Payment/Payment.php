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
 * @property text config
 */
class Payment extends Model
{
    //
}
