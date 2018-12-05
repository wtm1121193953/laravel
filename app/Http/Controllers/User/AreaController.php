<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 0:43
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Area\Area;
use App\Modules\Area\AreaService;
use App\Result;
use App\Support\Lbs;

class AreaController extends Controller
{

    public function getTree()
    {
        $tier = request('tier', 3);
        return Result::success(['list' => AreaService::getAsTree($tier)]);
    }

    public function getAreaByGps()
    {
        $this->validate(request(), [
            'lng' => 'required|numeric',
            'lat' => 'required|numeric',
        ]);
        $data = Lbs::getAreaByGps(request('lng'), request('lat'));
        return Result::success($data);
    }

    /**
     * 获取城市列表, 并根据首字母分组
     */
    public function getCityListGroupByFirstLetter()
    {
        $list = AreaService::getCityListGroupByFirstLetter();
        return Result::success($list);
    }

    /**
     * 获取包含热门城市的城市列表
     */
    public function getCitiesWithHot()
    {
        $cities = AreaService::getCityListGroupByFirstLetter();
        // 热门城市列表, 固定 : 北京 成都 重庆 广州 杭州 南京 上海 深圳 苏州 天津 武汉 西安
        $hotCityIds = [110100, 120100, 310100, 320100, 320500, 330100, 420100, 440100, 440300, 500100, 510100, 610100];
        $hotCities = Area::whereIn('area_id', $hotCityIds)
            ->get()
            ->each(function ($item){
                $item->name = str_replace('市', '', $item->name);
            })
            ->toArray();
        $data = [
            ['tag' => '热门', 'list' => $hotCities]
        ];
        foreach ($cities as $firstLetter => $list) {
            $data[] = ['tag' => $firstLetter, 'list' => $list];
        }
        return Result::success($data);
    }

    /**
     * 地区搜索
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function searchCityList()
    {
        $name = request('name');
        if(!$name){
            throw new BaseResponseException('请输入关键字');
        }
        $list = AreaService::getCityListByKeyword($name);
        if (empty($list)) {
            throw new BaseResponseException('暂不支持该地区');
        }
        return Result::success([
            'list' => $list,
        ]);
    }
}