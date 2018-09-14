<?php

/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 15:59
 */

namespace App\Modules\Oper;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Bizer\BizerService;

class OperBizerService extends BaseService {

    /**
     * 根据业务员获取运营中心
     * @param type $file
     * @return string
     * @throws BaseResponseException
     */
    public static function getBizerOper(array $data, bool $getWithQuery = false) {
        $bizer_id = array_get($data, "bizer_id");
        // 全局限制条件
        $query = OperBizer::where('status', 1)->orderByDesc('id');
        if (!empty($bizer_id)) {
            $query->where("bizer_id",$bizer_id);
        }
        if ($getWithQuery) {
            return $query;
        } else {
            $data = $query->paginate();
            //print_r($data);exit;
            $data->each(function ($item) {
                //echo $item->bizer_id;exit;
                $item->operName = Oper::where('id', $item->oper_id)->value('name');
                //$item->bizerName = Bizer::where('bizer_id', $item->bizer_id)->value('name');
            });
            return $data;
        }
    }

    /**
     * 根据id更新是否已读
     * @param $id
     * @param $isTips
     * @author tong.chen
     * @date 2018-08-23
     */
    public static function updateIsTipsById($id, $isTips = 1){
        OperBizer::where('id', $id)->update(['is_tips' => $isTips]);
    }
    
    /**
     *  获取列表
     * @param array $params 参数数组
     * @param array|string $fields 查询字段
     * @param bool $getOperInfo 是否获取运营中心信息
     * @return OperBizer[]
     * @author tong.chen
     * @date 2018-08-23
     */
    public static function getList($params, $fields = ['*'], $getOperInfo = true) {
        $bizerId = array_get($params, "bizer_id");
        $status = array_get($params, "status");
        $startTime = array_get($params, 'start_time');
        $endTime = array_get($params, 'end_time');
        $operIds = array_get($params, 'oper_ids');

        if (is_string($fields)) {
            $fields = explode(',', preg_replace('# #', '', $fields));
        }
        $data = OperBizer::when($bizerId, function (Builder $query) use ($bizerId) {
                    $query->where('bizer_id', $bizerId);
                })
                ->when(!empty($operIds), function (Builder $query) use ($operIds) {
                    if(is_array($operIds)){
                        $query->whereIn('oper_id', $operIds);
                    }else{
                        $query->where('oper_id', $operIds);
                    }
                })
                ->when(is_array($status), function (Builder $query) use ($status) {
                     $query->whereIn('status', $status);
                })
                ->when(is_numeric($status), function (Builder $query) use ($status) {
                     $query->where('status', $status);
                })
                ->when($startTime, function (Builder $query) use ($startTime) {
                    $query->where('created_at', '>=', $startTime);
                })
                ->when($endTime, function (Builder $query) use ($endTime) {
                    $query->where('created_at', '<', date('Y-m-d', strtotime('+1 day', strtotime($endTime))));
                })
                ->orderBy('id', 'desc')
                ->select($fields)
                ->paginate();
       
        if ($getOperInfo) {
            $data->each(function ($item) {
                $item->operInfo = OperService::getById($item->oper_id, 'name,contacter,tel,province,city') ?: null;
                $item->bizerInfo = BizerService::getById($item->bizer_id, 'name,mobile,status') ?: null;
            });
        }

        return $data;
    }

    /**
     * 获取所有业务员，不分页
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @internal param array $fields
     */
    public static function getAllbizer($params, $fields = ['*'])
    {
        $bizerId = array_get($params, "bizer_id");
        $status = array_get($params, "status");
        $startTime = array_get($params, 'start_time');
        $endTime = array_get($params, 'end_time');
        $operIds = array_get($params, 'oper_ids');

        if (is_string($fields)) {
            $fields = explode(',', preg_replace('# #', '', $fields));
        }
        $data = OperBizer::when($bizerId, function (Builder $query) use ($bizerId) {
            $query->where('bizer_id', $bizerId);
        })
            ->when(!empty($operIds), function (Builder $query) use ($operIds) {
                if(is_array($operIds)){
                    $query->whereIn('oper_id', $operIds);
                }else{
                    $query->where('oper_id', $operIds);
                }
            })
            ->when(is_array($status), function (Builder $query) use ($status) {
                $query->whereIn('status', $status);
            })
            ->when(is_numeric($status), function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->when($startTime, function (Builder $query) use ($startTime) {
                $query->where('created_at', '>=', $startTime);
            })
            ->when($endTime, function (Builder $query) use ($endTime) {
                $query->where('created_at', '<', date('Y-m-d', strtotime('+1 day', strtotime($endTime))));
            })
            ->orderBy('id', 'desc')
            ->select($fields)
            ->paginate();

            $data->each(function ($item) {
                $bizerInfo = BizerService::getById($item->bizer_id) ?: null;
                $item->bizerId = $bizerInfo->id;
                $item->bizerNme = $bizerInfo->name;
                $item->bizerMobile = $bizerInfo->mobile;
            });

        return $data;
    }
}