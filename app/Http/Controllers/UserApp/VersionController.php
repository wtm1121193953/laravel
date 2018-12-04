<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/15
 * Time: 17:12
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\BaseResponseException;
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
        $versionNo = request()->headers->get('version');

        throw new BaseResponseException('当前版本1.4.8');
        $lastVersion = VersionService::getLastVersion($appType, $versionNo);
        if(empty($lastVersion)){
            return Result::success('当前版本1.4.8');
        }

        $lastVersion->version = $lastVersion->version_no;
        return Result::success($lastVersion);
    }

    /**
     * 获取版本信息列表
     */
    public function getList()
    {
        $appType = request()->headers->get('app-type');
        $versionNo = request()->headers->get('version');

        $lastVersion = VersionService::getLastVersion($appType, $versionNo);

        $lastVersion->version = $lastVersion->version_no;

        $list = VersionService::getAllEnableVersionsByAppType($appType);
        $list->each(function($item){
            $item->version = $item->version_no;
        });

        return Result::success([
            'last' => $lastVersion,
            'versions' => $list
        ]);
    }
}