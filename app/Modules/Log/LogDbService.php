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
    public static function reapalPayRequest($type, $order_no, $request, $response)
    {
        if (is_array($request)) {
            $request = json_encode($request,JSON_UNESCAPED_UNICODE);
        }
        if (is_array($response)) {
            $response = json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        $log = new LogReapalPayRequest();
        $log->type = $type;
        $log->order_no = $order_no;
        $log->request = $request;
        $log->response = $response;
        $log->save();
    }

    public static function reapalNotify($type, $content)
    {

        if (is_array($content)) {
            $content = json_encode($content,JSON_UNESCAPED_UNICODE);
        }
        $log = new LogOrderNotifyReapal();
        $log->type = $type;
        $log->content = $content;
        return $log->save();
    }

    /**
     * 微信异步回调日志
     * @param $content
     * @return bool
     */
    public static function wechatNotify($content)
    {
        if (is_array($content)) {
            $content = json_encode($content,JSON_UNESCAPED_UNICODE);
        }

        $log = new LogOrderNotifyWechat();
        $log->content = $content;
        return $log->save();
    }

    /**
     * @param string $type
     * @param $request
     * @param $response
     * @param $mobile
     * @return bool
     */
    public static function paperMachineRequest($request, $response, $mobile ,$type='')
    {
        $log = new LogPaperMachineRequest();
        $log->request = $request;
        $log->response= $response;
        $log->mobile  = $mobile;
        $log->type    = empty($type) ? LogPaperMachineRequest::TYPE_POST : $type;
        return $log->save();
    }
}