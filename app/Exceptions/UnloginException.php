<?php
/**
 * User: Evan Lee
 * Date: 2017/6/29
 * Time: 14:49
 */

namespace App\Exceptions;


use App\ResultCode;

class UnloginException extends BaseResponseException
{


    public function __construct($message = "您尚未登录")
    {
        parent::__construct($message, ResultCode::UNLOGIN);
    }
}