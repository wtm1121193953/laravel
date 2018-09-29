<?php

namespace App\Modules\Version;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;


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

    public static function getAllByAppType($app_type)
    {
        return Version::where('app_type','=',$app_type)->orderBy('version_num','desc')->get();
    }

    public static function getLastIos()
    {
        return Version::where('app_type','=',Version::APP_TYPE_IOS)->orderBy('version_num','desc')->first();
    }

    public static function getLastAndroid()
    {
        return Version::where('app_type','=',Version::APP_TYPE_ANDROID)->orderBy('version_num','desc')->first();
    }
}
