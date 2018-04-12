<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 12:48
 */

namespace App\Exceptions;


use App\ResultCode;

class HasNotOpenIdException extends BaseResponseException
{

    public function __construct(string $message = "微信openId不存在")
    {
        parent::__construct($message, ResultCode::WECHAT_OPEN_ID_NOT_FOUND);
    }
}