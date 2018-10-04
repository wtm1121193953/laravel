<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exceptions\NoPermissionException;
use App\Http\Controllers\Controller;
use App\Modules\Version\Version;
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

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $data = VersionService::detail(request('id'));
        return Result::success($data);
    }

    public function add()
    {
        $this->validate(request(), [
            'app_name' => 'required',
            'app_tag' => 'required',
            'version_no' => 'required',
            'version_seq' => 'required|integer',
            'app_type' => 'required|in:1,2',
        ]);


        $data = [
            'app_name' => request('app_name'),
            'app_tag' => request('app_tag'),
            'version_no' => request('version_no'),
            'version_seq' => request('version_seq'),
            'desc' => request('desc', ''),
            'app_type' => request('app_type'),
            'status' => request('status', Version::STATUS_UNPUBLISH),
            'force' => request('force', 0),
            'app_size' => request('app_size', 0),
            'package_url' => request('package_url', ''),
        ];
        $version = VersionService::addVersion($data);
        return Result::success($version);
    }

    public function edit()
    {

        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'app_name' => 'required',
            'app_tag' => 'required',
            'version_no' => 'required',
            'version_seq' => 'required|integer',
            'app_type' => 'required|in:1,2',
        ]);

        $data = [
            'app_name' => request('app_name'),
            'app_tag' => request('app_tag'),
            'version_no' => request('version_no'),
            'version_seq' => request('version_seq'),
            'desc' => request('desc', ''),
            'app_type' => request('app_type'),
            'status' => request('status', Version::STATUS_UNPUBLISH),
            'force' => request('force', 0),
            'app_size' => request('app_size', 0),
            'package_url' => request('package_url', ''),
        ];
        $version = VersionService::editVersion(request('id'), $data);
        return Result::success($version);
    }

    public function del(){

        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);

        $version = Version::find(request('id'));
        if(empty($version)){
            throw new BaseResponseException('该版本信息已删除或不存在');
        }
        if($version->status == Version::STATUS_PUBLISHED){
            throw new NoPermissionException('该版本已发布, 不能删除');
        }
        $version->delete();
        return Result::success();
    }
}