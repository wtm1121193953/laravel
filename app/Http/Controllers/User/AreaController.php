<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 0:43
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
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
}