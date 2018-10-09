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
        $versionNo = array_get($params,'version_no');
        $versionSeq = array_get($params,'version_seq');
        $desc = array_get($params,'desc');
        $package_url = array_get($params,'package_url');
        $status = array_get($params,'status');
        $force = array_get($params,'force');
        $app_type = array_get($params,'app_type');
        $app_size = array_get($params,'app_size');

        $version = new Version();
        $version->app_name = $app_name;
        $version->app_tag = $app_tag;
        $version->version_no = $versionNo;
        $version->version_seq = $versionSeq;
        $version->desc = $desc;
        $version->package_url = $package_url ?? '';
        $version->status = $status;
        $version->force = $force;
        $version->app_type = $app_type;
        $version->app_size = $app_size;

        if( !$version->save() ){
            throw new BaseResponseException('添加版本信息失败');
        }
    }

    public static function editVersion(int $id, array $data)
    {
        $app_name = array_get($data,'app_name');
        $app_tag = array_get($data,'app_tag');
        $versionNo = array_get($data,'version_no');
        $versionSeq = array_get($data,'version_seq');
        $desc = array_get($data,'desc');
        $package_url = array_get($data,'package_url');
        $status = array_get($data,'status');
        $force = array_get($data,'force');
        $app_type = array_get($data,'app_type');
        $app_size = array_get($data,'app_size');

        $version =  Version::find($id);

        if(empty($version)){
            throw new DataNotFoundException('数据不存在或已被删除');
        }

        $version->app_name = $app_name;
        $version->app_tag = $app_tag;
        $version->version_no = $versionNo;
        $version->version_seq = $versionSeq;
        $version->desc = $desc;
        $version->package_url = $package_url ?? '';
        $version->status = $status;
        $version->force = $force;
        $version->app_type = $app_type;
        $version->app_size = $app_size;

        if( !$version->save() ){
            throw new BaseResponseException('修改版本信息失败');
        }
        return $version;
    }

    /**
     * 获取所有可用版本
     * @param $app_type
     * @return mixed
     */
    public static function getAllEnableVersionsByAppType(int $app_type)
    {
        return Version::where('app_type','=',$app_type)->where('status',Version::STATUS_PUBLISHED)->orderBy('version_seq','desc')->get();
    }
    
    /**
     * 获取最新版本号
     * @param int $app_type app类型
     * @param string $currentVersionNo 当前版本号
     * @return Version|null
     */
    public static function getLastVersion(int $app_type, string $currentVersionNo = '')
    {
        $app_types = [Version::APP_TYPE_ANDROID,Version::APP_TYPE_IOS];

        if (!in_array($app_type, $app_types)) {
            throw new ParamInvalidException('参数错误');
        }

        // 获取当前版本
        $currentVersion = Version::where('app_type', $app_type)
            ->where('version_no','=', $currentVersionNo)
            ->first();
        if (empty($currentVersion)) {
            return self::getLastVersionByType($app_type);
        }

        $newVersions = Version::where('app_type','=', $app_type)
            ->where('status',Version::STATUS_PUBLISHED)
            ->where('version_seq','>', $currentVersion->version_seq)
            ->orderBy('version_seq','desc')
            ->get();

        if($newVersions->count() <= 0){
            return null;
        }

        $lastVersion = $newVersions->first();
        if($lastVersion->force == 0){
            foreach ($newVersions as $l) {
                if ($l['force'] == 1) {
                    $lastVersion->force = 1;
                    break;
                }
            }
        }

        return $lastVersion;

    }


    public static function getLastVersionByType(int $app_type) {
        $app_types = [Version::APP_TYPE_ANDROID,Version::APP_TYPE_IOS];

        if (!in_array($app_type, $app_types)) {
            throw new ParamInvalidException('参数错误');
        }

        $lastVersion = Version::where('app_type','=', $app_type)
            ->where('status',Version::STATUS_PUBLISHED)
            ->orderBy('version_seq','desc')
            ->first();

        return $lastVersion;
    }

}
