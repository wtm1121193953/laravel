<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/9/28
 * Time: 19:23
 */

namespace App\Support\Daqian;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DaqianApi
{

    /**
     * get方法请求
     * @param string $appKey
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return array
     * @throws ApiRequestException
     */
    public static function get($appKey, $url, $params = [], $headers = [])
    {
        // 1. 签名参数
        $sign = self::sign($appKey, $params);
        $params['sign'] = $sign;
        // 2. 发起请求
        $result = self::request('get', $url, $params, $headers);
        return $result;
    }

    /**
     * post请求
     * @param string $appKey
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return mixed
     * @throws ApiRequestException
     */
    public static function post($appKey, $url, $params = [], $headers = [])
    {

        // 1. 签名参数
        $sign = self::sign($appKey, $params);
        $params['sign'] = $sign;
        // 2. 发起请求
        $result = self::request('post', $url, $params, $headers);
        return $result;
    }

    /**
     * 发起请求
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return array
     * @throws ApiRequestException
     */
    private static function request($method, $url, array $params = [], array $headers = [])
    {
        $client = new Client();
        if($method == 'get'){
            $response = $client->get($url, [
                'query' => $params,
                'headers' => $headers,
            ]);
        }else {
            $response = $client->post($url, [
                'form_params' => $params,
                'headers' => $headers,
            ]);
        }
        if($response->getStatusCode() != 200){
            Log::error('接口请求失败:' . $url, compact('url', 'params', 'headers'));
            throw new ApiRequestException('接口请求失败: ' . $url, compact('url', 'params', 'headers'));
        }
        $content = $response->getBody()->getContents();
        $result = json_decode($content, 1);

        if(!$result){
            throw new ApiRequestException('接口未返回合法的json, 返回结果: ' . $content);
        }
        /*if(!isset($result['code']) || $result['code'] != 0){
            throw new ApiRequestException('接口处理失败', $result);
        }*/
        return $result;
    }

    /**
     * 默认的返回结果处理
     * @param array $result
     * @throws ApiRequestException
     */
    protected static function defaultHandleResult(array $result){
        if(!isset($result['code']) || $result['code'] != 0){
            throw new ApiRequestException('接口处理失败:' . $result['message'], $result);
        }
    }

    /**
     * 接口参数签名
     * @param $appKey
     * @param array $params
     * @return string
     */
    private static function sign($appKey, array $params)
    {
        asort($params);
        $arr = [];
        foreach ($params as $key => $value){
            if($key == 'sign') continue;
            $arr[] = $key . '=' . $value;
        }
        $string = implode('&', $arr);
        $string .= '&key=' . $appKey;
        return md5($string);
    }

    /**
     * 根据请求的参数验签
     * @param $appKey
     * @param array $params
     * @return bool
     */
    public static function checkSign($appKey, array $params)
    {
        if(!isset($params['sign']) || !$params['sign']){
            return false;
        }
        $sign = self::sign($appKey, $params);
        return $sign == $params['sign'];
    }

}