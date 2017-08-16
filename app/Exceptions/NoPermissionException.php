<?php
/**
 * User: Evan Lee
 * Date: 2017/6/21
 * Time: 11:31
 */

namespace App\Exceptions;


use App\ResultCode;

class NoPermissionException extends BaseResponseException
{


    public function __construct($message = "您无权限进行该操作")
    {
        parent::__construct($message, ResultCode::PERMISSION_NOT_ALLOWED);
    }
}