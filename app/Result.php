<?php
/**
 * User: Evan Lee
 * Date: 2017/6/27
 * Time: 11:09
 */

namespace App;



use Illuminate\Contracts\Support\Arrayable;

class Result
{

    public static function success($message = '请求成功', $data = []){
        if(is_array($message) or $message instanceof Arrayable){
            $data    = $message;
            $message = '请求成功';
        }
        return response([
            'code'    => ResultCode::SUCCESS,
            'message' => $message,
            'data'    => $data,
            'timestamp' => time(),
        ]);
    }

    public static function error($code, $message, $data = [])
    {
        return response([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
            'timestamp' => time(),
        ]);
    }
}