<?php
/**
 * User: Evan Lee
 * Date: 2017/6/21
 * Time: 11:31
 */

namespace App\Exceptions;


use App\ResultCode;

class AccountNotFoundException extends BaseResponseException
{


    public function __construct($message = "帐号不存在")
    {
        parent::__construct($message, ResultCode::ACCOUNT_NOT_FOUND);
    }
}