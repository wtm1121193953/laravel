<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/14
 * Time: 15:55
 */

namespace App\Http\Controllers\Bizer;


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