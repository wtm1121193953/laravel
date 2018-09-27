<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Version\VersionService;
use App\Result;

class VersionController extends Controller
{

    /**
     * 获取版本列表
     */
    public function getList()
    {
        $app_type = request()->get('app_type');
        $app_name = request()->get('app_name');
        $params = [
            'app_name' => $app_name,
            'app_type' => $app_type
        ];
        $data = VersionService::getList($params);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add()
    {
        $app_name = request()->get('app_name');
        $app_tag = request()->get('app_tag');
        $app_num = request()->get('app_num');
        $version_num = request()->get('version_num');
        $version_explain = request()->get('version_explain');
        $package_url = request()->get('package_url');
        $status = request()->get('status');
        $force_update = request()->get('force_update');
        $app_type = request()->get('app_type');

        $params = [
            'app_name' => $app_name,
            'app_tag' => $app_tag,
            'app_num' => $app_num,
            'version_num' => $version_num,
            'version_explain' => $version_explain,
            'package_url' => $package_url,
            'status' => $status,
            'force_update' => $force_update,
            'app_type' => $app_type
        ];
        $data = VersionService::addVersion($params);
        return Result::success($data);
    }
}