<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/25/025
 * Time: 16:36
 */
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Modules\Setting\SettingService;
use App\Result;

class SettingController extends Controller
{
    /**
     * app通用配置
     * @return mixed
     */
    public function settings()
    {

        return Result::success([
            'supermarket_on' => SettingService::getValueByKey('supermarket_on'),
            'index_cs_banner_on' => SettingService::getValueByKey('supermarket_index_cs_banner_on'),
            'index_cs_banner_url' => 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/6352c1aecba7dd10fcc87c32bf3d92a1.png'
        ]);
    }
}