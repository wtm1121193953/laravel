<?php

namespace App\Modules\Version;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;


class VersionService extends BaseService
{
    public static function getList(array $params)
    {
        $query = Version::query();

        $app_name = array_get($params,'app_name');
        $app_type = array_get($params,'app_type');
        if($app_name){
            $query->where('app_name','like',"%".$app_name."%");
        }
        if($app_type){
            $query->where('app_type',$app_type);
        }
        $query->orderBy('id','desc');
        $data = $query->paginate();
        return $data;
    }

    public static function detail($id)
    {
        $oper = Version::find($id);
        return $oper;
    }

    public static function addVersion(array $params)
    {
        $app_name = array_get($params,'app_name');
        $app_tag = array_get($params,'app_tag');
        $app_num = array_get($params,'app_num');
        $version_num = array_get($params,'version_num');
        $version_explain = array_get($params,'version_explain');
        $package_url = array_get($params,'package_url');
        $status = array_get($params,'status');
        $force_update = array_get($params,'force_update');
        $app_type = array_get($params,'app_type');
        $app_size = array_get($params,'app_size');

        $query = new Version();
        $query->app_name = $app_name;
        $query->app_tag = $app_tag;
        $query->app_num = $app_num;
        $query->version_num = $version_num;
        $query->version_explain = $version_explain;
        $query->package_url = $package_url ?? '';
        $query->status = $status;
        $query->force_update = $force_update;
        $query->app_type = $app_type;
        $query->app_size = $app_size;

        if( !$query->save() ){
            throw new BaseResponseException('添加版本信息失败');
        }
    }

    public static function editVersion(array $params)
    {
        $id = array_get($params,'id');
        $app_name = array_get($params,'app_name');
        $app_tag = array_get($params,'app_tag');
        $app_num = array_get($params,'app_num');
        $version_num = array_get($params,'version_num');
        $version_explain = array_get($params,'version_explain');
        $package_url = array_get($params,'package_url');
        $status = array_get($params,'status');
        $force_update = array_get($params,'force_update');
        $app_type = array_get($params,'app_type');
        $app_size = array_get($params,'app_size');

        $query =  Version::find($id);

        if(empty($query)){
            throw new DataNotFoundException('数据不存在或已被删除');
        }

        $query->app_name = $app_name;
        $query->app_tag = $app_tag;
        $query->app_num = $app_num;
        $query->version_num = $version_num;
        $query->version_explain = $version_explain;
        $query->package_url = $package_url ?? '';
        $query->status = $status;
        $query->force_update = $force_update;
        $query->app_type = $app_type;
        $query->app_size = $app_size;

        if( !$query->save() ){
            throw new BaseResponseException('修改版本信息失败');
        }
        return $query;
    }

    /**
     * 获取所有可用版本
     * @param $app_type
     * @return mixed
     */
    public static function getAllByAppType(int $app_type)
    {
        return Version::where('app_type','=',$app_type)->where('status','=','2')->orderBy('version_num','desc')->get();
    }
    
    /**
     * 获取最新版本号
     * @param int $app_type app类型
     * @param string $app_num_now 当前版本号
     * @return array
     */
    public static function getLastVersion(int $app_type, string $app_num_now='')
    {
        $app_types = [Version::APP_TYPE_ANDROID,Version::APP_TYPE_IOS];

        if (!in_array($app_type, $app_types)) {
            throw new ParamInvalidException('参数错误');
        }

        $version_num_now = 0;
        if ($app_num_now) {
            $version_now = Version::where('app_type','=', $app_type)
                ->where('app_num','=',$app_num_now)
                ->first();

            if (empty($version_now)) {
                throw new ParamInvalidException('当前版本号错误');
            }
            $version_num_now = $version_now['version_num'];
        }


        $list = Version::where('app_type','=', $app_type)
            ->where('status',Version::STATUS_PUBLISHED)
            ->where('version_num','>',$version_num_now)
            ->orderBy('version_num','desc')
            ->get()->toArray();

        if (empty($list)) {
            throw new ParamInvalidException('已经是最新版本');
        }


        $rt = $list[0];
        $force_update = 0;
        foreach ($list as $l) {
            if ($l['force_update'] == 1) {
                $force_update = 1;
                break;
            }
        }

        $rt['force_update'] = $force_update;
        return $rt;

    }

}
