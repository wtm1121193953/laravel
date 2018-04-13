<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 15:33
 */

namespace App\Support;

use App\Modules\Area\Area;


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
        $area = Area::where('area_id', $areaCode)->firstOrFail();
        $city = Area::where('area_id', substr($areaCode, 0, 4) . '00')->firstOrFail();
        $province = Area::where('area_id', substr($areaCode, 0, 2))->firstOrFail();

        return [
            'area' => $area->name,
            'area_id' => $area->area_id,
            'city' => $city->name,
            'city_id' => $city->area_id,
            'province' => $province->name,
            'province_id' => $province->area_id,
        ];
    }

}