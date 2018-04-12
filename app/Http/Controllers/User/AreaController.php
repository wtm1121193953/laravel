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

class AreaController extends Controller
{

    public function getTree()
    {
        $tier = request('tier', 3);
        return Result::success(['list' => AreaService::getAsTree($tier)]);
    }
}