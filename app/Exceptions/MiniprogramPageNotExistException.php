<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/19
 * Time: 15:20
 */

namespace App\Exceptions;


use App\ResultCode;

class MiniprogramPageNotExistException extends BaseResponseException
{

    public function __construct(string $message = "小程序页面不存在或尚未发布", int $code = ResultCode::MINIPROGRAM_PAGE_NOT_EXIST)
    {
        parent::__construct($message, $code);
    }
}