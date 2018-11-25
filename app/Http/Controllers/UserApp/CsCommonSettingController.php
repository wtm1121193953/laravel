<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/25/025
 * Time: 16:36
 */
namespace App\Http\Controllers\UserApp;

use App\Http\Controllers\Controller;
use App\Result;

class CsCommonSettingController extends Controller
{
    /**
     * 超市通用配置
     * @return mixed
     */
    public function settings()
    {

        return Result::success([
            'supermarket_on' => config('common.supermarket_on'),
        ]);
    }
}