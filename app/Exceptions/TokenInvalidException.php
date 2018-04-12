<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 11:59
 */

namespace App\Exceptions;


use App\ResultCode;

class TokenInvalidException extends BaseResponseException
{

    public function __construct($message = "token无效")
    {
        parent::__construct($message, ResultCode::TOKEN_INVALID);
    }

}