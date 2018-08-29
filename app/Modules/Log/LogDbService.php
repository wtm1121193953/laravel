<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/8/29/029
 * Time: 14:48
 */

namespace App\Modules\Log;


class LogDbService
{

    /**
     * 融宝请求日志
     * @param $type
     * @param $order_no
     * @param $requst
     * @param $response
     */
    public static function reapalPayRequest($type, $order_no, $requst, $response)
    {
        if (is_array($requst)) {
            $requst = json_encode($requst);
        }
        if (is_array($response)) {
            $response = json_encode($response);
        }
        $log = new LogReapalPayRequest();
        $log->type = $type;
        $log->order_no = $order_no;
        $log->$requst = $requst;
        $log->$response = $response;
        $log->save();
    }
}