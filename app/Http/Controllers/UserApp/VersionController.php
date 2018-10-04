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
        $version = request()->headers->get('version');

        $lastVersion = VersionService::getLastVersion($appType, $version);
        if(empty($lastVersion)){
            return Result::success('已经是最新版本');
        }

        return Result::success([
            'version' => $lastVersion['app_num'],
            'force' => $lastVersion['force_update'],
            'desc' => $lastVersion['version_explain'],
            'app_type' => $lastVersion['app_type'],
            'package_url' => $lastVersion['package_url'],
            'app_size' => $lastVersion['app_size'],
        ]);
    }

    /**
     * 获取版本信息列表
     */
    public function getList()
    {
        $appType = request()->headers->get('app-type');
        $version = request()->headers->get('version');

        $data = VersionService::getLastVersion($appType, $version);

        $last = [
            'version' => $data['app_num'],
            'force' => $data['force_update'],
            'desc' => $data['version_explain'],
            'app_type' => $data['app_type'],
        ];

        $list = VersionService::getAllEnableVersionsByAppType($appType);
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