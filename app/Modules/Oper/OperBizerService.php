<?php

namespace App\Modules\Oper;

use App\BaseService;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Bizer\BizerService;

class OperBizerService extends BaseService {

    /**
     * 根据业务员获取运营中心
     * @param array $data
     * @param bool $getWithQuery
     * @return OperBizer|array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getBizerOper(array $data, bool $getWithQuery = false) {
        $bizer_id = array_get($data, "bizer_id");
        // 全局限制条件
        $query = OperBizer::where('status', OperBizer::STATUS_SIGNED)->orderByDesc('id');
        if (!empty($bizer_id)) {
            $query->where("bizer_id",$bizer_id);
        }
        if ($getWithQuery) {
            return $query;
        } else {
            $data = $query->paginate();
            $data->each(function ($item) {
                $item->operName = Oper::where('id', $item->oper_id)->value('name');
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
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
     * @param array $fields
     * @internal param array $fields
     * @return OperBizer[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllbizer($params, $fields = ['*'])
    {
        $bizerId = array_get($params, "bizer_id");
        $status = array_get($params, "status");
        $sign_status = array_get($params, 'sign_status');
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
            ->when($sign_status, function (Builder $query) use ($sign_status) {
                $query->where('sign_status', $sign_status);
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
            ->get();

            $data->each(function ($item) {
                $bizerInfo = BizerService::getById($item->bizer_id) ?: null;
                $item->bizerId = $bizerInfo->id;
                $item->bizerNme = $bizerInfo->name;
                $item->bizerMobile = $bizerInfo->mobile;
            });

        return $data;
    }

    /**
     * 通过参数获取运营中心与业务员的关联信息
     * @param $params
     * @return Builder|\Illuminate\Database\Eloquent\Model|null|object|OperBizer
     */
    public static function getOperBizerByParam($params)
    {
        $operId = array_get($params, 'operId', 0);
        $bizerId = array_get($params, 'bizerId', 0);

        $query = OperBizer::query();
        if ($operId) {
            $query->where('oper_id', $operId);
        }
        if ($bizerId) {
            $query->where('bizer_id', $bizerId);
        }
        $operBizer = $query->first();

        return $operBizer;
    }

    /**
     * 通过id获取运营中心业务员
     * @param $id
     * @return OperBizer
     */
    public static function getById($id)
    {
        $operBizer = OperBizer::find($id);

        return $operBizer;
    }

    /**
     * 通过姓名和手机号码获取员工推荐码
     * @param $memberNameOrMobile
     * @return \Illuminate\Support\Collection|string
     */
    public static function getOperBizMemberCodeByNameOrMobile($memberNameOrMobile)
    {
        if ($memberNameOrMobile) {
            $operBizMemberCodes = OperBizMember::where('name', 'like', "%$memberNameOrMobile%")
                ->orWhere('mobile', 'like', "%$memberNameOrMobile%")
                ->select('code')
                ->get()
                ->pluck('code');
        } else {
            $operBizMemberCodes = '';
        }

        return $operBizMemberCodes;
    }
}
