<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/9/12
 * Time: 20:06
 */

namespace App\Http\Controllers\Developer;


use App\Http\Controllers\Controller;
use App\Result;

class ConfigController extends Controller
{
    public function getList()
    {
        return Result::success(config()->all());
    }
}