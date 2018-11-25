<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 15:33
 */

namespace App\Support;

use App\Exceptions\BaseResponseException;
use App\Modules\Area\Area;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


/**
 * 位置服务
 * Class LBS
 * @package App\Support
 */
class Lbs
{

    /**
     * 根据经纬度获取所在地区
     * @param $lng
     * @param $lat
     * @return array 返回一个包含省市县的名称及ID数组
     */
    public static function getAreaByGps($lng, $lat)
    {
        $amap = new Amap();
        $result = $amap->regeo($lng, $lat);
        $addressArr = $result['addressComponent'];
        $areaCode = $addressArr['adcode'];
        if(empty($areaCode)){
            Log::error('高德地图逆地理解析错误', compact('lng', 'lat', 'result'));
            throw new BaseResponseException('定位当前城市失败');
        }
        $area = Area::where('area_id', $areaCode)->first();
        $city = Area::where('area_id', substr($areaCode, 0, 4) . '00')->first();
        $province = Area::where('area_id', substr($areaCode, 0, 2))->first();
        if(empty($area) || empty($city) || empty($province)){
            Log::error('根据经纬度获取位置信息失败', compact('lng', 'lat', 'result'));
            throw new BaseResponseException('定位当前城市失败');
        }
        $data = [];
        if($area){
            $data['area'] = $area->name;
            $data['area_id'] = $area->area_id;
        }
        if($city){
            $data['city'] = $city->name;
            $data['city_id'] = $city->area_id;
        }
        if($province){
            $data['province'] = $province->name;
            $data['province_id'] = $province->area_id;
        }

        return $data;
    }

    /**
     * 添加商家GPS信息, 可批量添加
     * @param $id int|Collection|array[{merchantId, lng, lat}]
     * @param $lng
     * @param $lat
     */
    public static function merchantGpsAdd($id, $lng=null, $lat=null)
    {
        if(is_array($id) || $id instanceof Collection){
            $params = [];
            foreach ($id as $item) {
                $params[] = [$item->lng, $item->lat, $item->id];
            }
            $params = array_flatten($params);
        }else {
            $params = [$lng, $lat, $id];
        }
        Redis::geoadd('location:merchant', ...$params);
    }

    /**
     * 返回用户与商户的距离
     * @param $merchantId
     * @param $userId
     * @param $userLng
     * @param $userLat
     * @param string $unit 单位, 取值: m, km 默认: m(米)
     * @return null|int
     */
    public static function getDistanceOfMerchant($merchantId, $userId, $userLng, $userLat, $unit = 'm')
    {
        $userGpsKey = 'computed_user_' . $userId;
        Redis::geoadd('location:merchant', $userLng, $userLat, $userGpsKey);
        $distance = Redis::geodist('location:merchant', $merchantId, $userGpsKey, $unit);
        return intval($distance);
    }

    /**
     * 根据经纬度获取最近的商家距离
     * @param $lng
     * @param $lat
     * @param int $radius 距离, 单位: 米
     * @param string $sort
     * @return array 返回数据的键为商家ID, 值为距离
     */
    public static function getNearlyMerchantDistanceByGps($lng, $lat, $radius = 3000, $sort = 'asc')
    {
        $result = Redis::georadius('location:merchant', $lng, $lat, $radius, 'm', 'WITHDIST', $sort);

        $arr = [];
        foreach ($result as $item) {
            if(is_numeric($item[0])){
                $arr[$item[0]] = intval($item[1]);
            }
        }
        return $arr;
    }


    /**
     * 添加超市商家GPS信息, 可批量添加
     * @param $id int|Collection|array[{merchantId, lng, lat}]
     * @param $lng
     * @param $lat
     */
    public static function csMerchantGpsAdd($id, $lng=null, $lat=null)
    {
        if(is_array($id) || $id instanceof Collection){
            $params = [];
            foreach ($id as $item) {
                $params[] = [$item->lng, $item->lat, $item->id];
            }
            $params = array_flatten($params);
        }else {
            $params = [$lng, $lat, $id];
        }
        Redis::geoadd('location:cs_merchant', ...$params);
    }

    /**
     * 返回用户与超市商户的距离
     * @param $merchantId
     * @param $userId
     * @param $userLng
     * @param $userLat
     * @param string $unit 单位, 取值: m, km 默认: m(米)
     * @return null|int
     */
    public static function getDistanceOfCsMerchant($merchantId, $userId, $userLng, $userLat, $unit = 'm')
    {
        $userGpsKey = 'computed_user_' . $userId;
        Redis::geoadd('location:cs_merchant', $userLng, $userLat, $userGpsKey);
        $distance = Redis::geodist('location:cs_merchant', $merchantId, $userGpsKey, $unit);
        return intval($distance);
    }

    /**
     * 根据经纬度获取最近的超市商家距离
     * @param $lng
     * @param $lat
     * @param int $radius 距离, 单位: 米
     * @param string $sort
     * @return array 返回数据的键为商家ID, 值为距离
     */
    public static function getNearlyCsMerchantDistanceByGps($lng, $lat, $radius = 3000, $sort = 'asc')
    {
        $result = Redis::georadius('location:cs_merchant', $lng, $lat, $radius, 'm', 'WITHDIST', $sort);

        $arr = [];
        foreach ($result as $item) {
            if(is_numeric($item[0])){
                $arr[$item[0]] = intval($item[1]);
            }
        }
        return $arr;
    }

}