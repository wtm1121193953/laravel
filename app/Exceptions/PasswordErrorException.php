<?php
/**
 * User: Evan Lee
 * Date: 2017/6/21
 * Time: 15:52
 */

namespace App\Exceptions;


use App\ResultCode;

class PasswordErrorException extends BaseResponseException
{

    public function __construct($message = "帐号密码错误")
    {
        parent::__construct($message, ResultCode::ACCOUNT_PASSWORD_ERROR);
    }

}