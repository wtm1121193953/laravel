<?php
/**
 * User: Evan Lee
 * Date: 2017/6/27
 * Time: 11:30
 */

namespace App\Exceptions;


use App\ResultCode;

class DataNotFoundException extends BaseResponseException
{

    public function __construct($message = "数据不存在")
    {
        parent::__construct($message, ResultCode::DB_QUERY_FAIL);
    }
}