<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 15:37
 */

namespace App\Support;
use App\Exceptions\BaseResponseException;
use GuzzleHttp\Client;


/**
 * 高德地图api封装
 * Class Amap
 * @package App\Support
 */
class Amap
{
    protected $baseUrl = 'http://restapi.amap.com/v3';
    protected $key;
    protected $client;


    public function __construct()
    {
        $config = config('map.amap');
        $this->key = $config['key'];
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
        ]);
    }

    public function get($url, $query = [])
    {
        $query['key'] = $this->key;

        $response = $this->client->get($url, [
            'query' => $query,
        ]);
        if($response->getStatusCode() != 200){
            throw new BaseResponseException('地图接口请求失败');
        }
        $result = $response->getBody()->getContents();
        return is_string($result) ? json_decode($result, 1) : $result;
    }

    public function post($url, $data)
    {
        $data['key'] = $this->key;

        $response = $this->client->post($url, [
            'form_params' => $data,
        ]);
        if($response->getStatusCode() != 200){
            throw new BaseResponseException('地图接口请求失败');
        }
        $result = $response->getBody()->getContents();
        return is_string($result) ? json_decode($result, 1) : $result;
    }

    /**
     * 逆地理编码, 通过经纬度信息获取地址信息
     */
    public function regeo($lng, $lat)
    {
        $url = "$this->baseUrl/geocode/regeo";
        $result = $this->get($url, [
            'location' => "$lng,$lat",
        ]);
        if($result['status'] != 1){
            throw new BaseResponseException('高德地图api返回错误: ' . $result['info'] );
        }
        return $result['regeocode'];
    }

    public function getDistrict($keywords='中华人民共和国', $subdistrict=3)
    {
        $url = "$this->baseUrl/config/district";
        $result = $this->get($url, [
            'keywords' => $keywords,
            'subdistrict' => $subdistrict,
            'extensions' => 'base'
        ]);
        if($result['status'] != 1){
            throw new BaseResponseException('高德地图api返回错误: ' . $result['info'] );
        }
        return $result['districts'];
    }
}