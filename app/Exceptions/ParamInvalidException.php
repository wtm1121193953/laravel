<?php
/**
 * User: Evan Lee
 * Date: 2017/6/20
 * Time: 19:23
 */

namespace App\Exceptions;


use App\ResultCode;

class ParamInvalidException extends BaseResponseException
{

    public function __construct($message = "参数不合法")
    {
        parent::__construct($message, ResultCode::PARAMS_INVALID);
    }
}