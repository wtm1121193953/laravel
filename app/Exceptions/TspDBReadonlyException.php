<?php
/**
 * User: Evan Lee
 * Date: 2017/6/29
 * Time: 14:49
 */

namespace App\Exceptions;


use App\ResultCode;

class TspDBReadonlyException extends BaseResponseException
{


    public function __construct($message = "业务平台数据库只读")
    {
        parent::__construct($message, ResultCode::TSP_DB_READONLY);
    }
}