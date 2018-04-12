<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:06
 */

namespace App\Support;


use App\Exceptions\BaseResponseException;
use App\Result;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MicroServiceApi
{
    public static function get($url, $data)
    {

        $client = new Client();
        $response = $client->post($url, [
            'query' => $data
        ]);
        if($response->getStatusCode() !== 200){
            Log::error('微服务网络请求失败', compact('url', 'data'));
            throw new BaseResponseException("网络请求失败");
        }
        $result = $response->getBody()->getContents();
        $array = is_string($result) ? json_decode($result, 1) : $result;
        return $array;
    }

    public static function post($url, $data)
    {
        $client = new Client();
        $response = $client->post($url, [
            'form_params' => $data
        ]);
        if($response->getStatusCode() !== 200){
            Log::error('微服务网络请求失败', compact('url', 'data'));
            throw new BaseResponseException("网络请求失败");
        }
        $result = $response->getBody()->getContents();
        $array = is_string($result) ? json_decode($result,1 ) : $result;
        return $array;
    }
}