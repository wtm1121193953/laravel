<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/15
 * Time: 17:12
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Version\VersionService;
use App\Result;

class VersionController extends Controller
{

    /**
     * 获取最新版本
     */
    public function last()
    {
        $appType = request()->headers->get('app-type');

        if (!in_array($appType,[1,2])) {
            throw new ParamInvalidException('参数错误');
        }
        if ($appType == 2 ) {
            $data = VersionService::getLastIos();
        } else {
            $data = VersionService::getLastAndroid();
        }
        return Result::success([
            'version' => $data['app_num'],
            'force' => $data['force_update'],
            'desc' => $data['version_explain'],
            'app_type' => $data['app_type'],
            'package_url' => $data['package_url'],
            'app_size' => $data['app_size'],
        ]);
    }

    /**
     * 获取版本信息列表
     */
    public function getList()
    {
        $appType = request()->headers->get('app-type');

        if (!in_array($appType,[1,2])) {
            throw new ParamInvalidException('参数错误');
        }
        if ($appType == 2 ) {
            $data = VersionService::getLastIos();
        } else {
            $data = VersionService::getLastAndroid();
        }

        $last = [
            'version' => $data['app_num'],
            'force' => $data['force_update'],
            'desc' => $data['version_explain'],
            'app_type' => $data['app_type'],
        ];

        $list = VersionService::getAllByAppType(['app_type' => $appType]);
        $versions = [];
        if ($list) {
            foreach ($list as $l) {
                $versions[] = [
                    'version' => $l['app_num'],
                    'force' => $l['force_update'],
                    'desc' => $l['version_explain'],
                    'app_type' => $l['app_type'],
                ];
            }
        }


        return Result::success([
            'last' => $last,
            'versions' => $versions
        ]);
    }
}