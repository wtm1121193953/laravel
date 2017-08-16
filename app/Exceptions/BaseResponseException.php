<?php
/**
 * User: Evan Lee
 * Date: 2017/6/28
 * Time: 14:20
 */

namespace App\Exceptions;


use App\ResultCode;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 业务异常基类，没有具体异常匹配时使用此基类
 *
 * Class BaseResponseException
 * @package App\Exceptions
 */
class BaseResponseException extends HttpResponseException
{

    public function __construct($message = "未知错误", $code = ResultCode::UNKNOWN)
    {
        $response = response([
            'code' => $code,
            'message' => $message
        ]);
        parent::__construct($response);
    }
}