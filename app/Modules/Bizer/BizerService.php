<?php

namespace App\Modules\Bizer;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Oper\OperBizer;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperService;
use App\ResultCode;
use Illuminate\Database\Eloquent\Builder;

class BizerService extends BaseService
{


    /**
     * 查询所有业务员，不分页
     * @param array $data
     * @param bool $getWithQuery
     * @return Bizer|Bizer[]|array|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAll(array $data, bool $getWithQuery = false)
    {
        $status = array_get($data,"status");
        // 全局限制条件
        $query = Bizer::where('status', $status)->orderByDesc('id');

        if ($getWithQuery) {
            return $query;
        } else {

            $data = $query->get();

            return $data;
        }
    }

    /**
     * 通过id获取业务员
     * @param $id
     * @param array $fields
     * @return Bizer
     */
    public static function getById($id, $fields = ['*'])
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        $bizer = Bizer::find($id, $fields);
        return $bizer;
    }

    /**
     * 添加业务员审核表 记录
     * @param $data
     * @param Bizer $bizer
     * @return BizerIdentityAuditRecord
     */
    public static function addBizerIdentityAuditRecord($data, Bizer $bizer)
    {
        $bizerIdentityAuditRecord = new BizerIdentityAuditRecord();
        $bizerIdentityAuditRecord->bizer_id = $bizer->id;
        $bizerIdentityAuditRecord->name = $data['name'];
        $bizerIdentityAuditRecord->id_card_no = $data['idCardNo'];
        $bizerIdentityAuditRecord->front_pic = $data['frontPic'];
        $bizerIdentityAuditRecord->opposite_pic = $data['oppositePic'];
        $bizerIdentityAuditRecord->status = BizerIdentityAuditRecord::STATUS_AUDIT_PREPARE;
        $bizerIdentityAuditRecord->reason = '';
        $bizerIdentityAuditRecord->update_user = 0;
        $bizerIdentityAuditRecord->save();
        return $bizerIdentityAuditRecord;
    }

    /**
     * 通过业务员id 获取业务员审核信息
     * @param $bizerId
     * @return BizerIdentityAuditRecord
     */
    public static function getBizerIdentityAuditRecordByBizerId($bizerId)
    {
        $bizerIdentityAuditRecord = BizerIdentityAuditRecord::where('bizer_id', $bizerId)->first();
        return $bizerIdentityAuditRecord;
    }

    /**
     * 修改业务员审核信息
     * @param $data
     * @param BizerIdentityAuditRecord $bizerIdentityAuditRecord
     * @return BizerIdentityAuditRecord
     */
    public static function editBizerIdentityAuditRecord($data, BizerIdentityAuditRecord $bizerIdentityAuditRecord)
    {
        $exist = self::checkRecordCardNoUsed($bizerIdentityAuditRecord->bizer_id, $data['idCardNo']);
        if( $exist ) {
            throw new BaseResponseException( '该身份证号已被他人使用!');
        }

        $bizerIdentityAuditRecord->name = $data['name'];
        $bizerIdentityAuditRecord->id_card_no = $data['idCardNo'];
        $bizerIdentityAuditRecord->front_pic = $data['frontPic'];
        $bizerIdentityAuditRecord->opposite_pic = $data['oppositePic'];
        $bizerIdentityAuditRecord->status = BizerIdentityAuditRecord::STATUS_AUDIT_PREPARE;
        if (!$bizerIdentityAuditRecord->save()) {
            throw new BaseResponseException('修改失败证件信息失败', ResultCode::DB_UPDATE_FAIL);
        }
        return $bizerIdentityAuditRecord;
    }

    /**
     * 根据id获取业务员审核信息
     * @param $id
     * @return BizerIdentityAuditRecord
     */
    public static function getBizerIdentityAuditRecordById($id)
    {
        $bizerIdentityAuditRecord = BizerIdentityAuditRecord::find($id);
        return $bizerIdentityAuditRecord;
    }

    /**
     * 判断身份证号码是否存在
     * @param $bizerId
     * @param $cardNo
     * @return bool
     */
    public static function checkRecordCardNoUsed($bizerId, $cardNo)
    {
        $exist=  BizerIdentityAuditRecord::where('id_card_no', $cardNo)
            ->where('bizer_id', '!=', $bizerId)->exists();
        return $exist;
    }

    /**
     * 获取业务员列表
     * @param $params
     * @param int $pageSize
     * @param bool $withQuery
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder
     */
    public static function getBizerList($params, $pageSize = 15, $withQuery = false)
    {
        $mobile = array_get($params, 'mobile', '');
        $id = array_get($params, 'id', 0);
        $name = array_get($params, 'name', '');
        $identityName = array_get($params, 'identityName', '');
        $bizerStartDate = array_get($params, 'startDate', '');
        $bizerEndDate = array_get($params, 'endDate', '');
        $status = array_get($params, 'status', 0);
        $identityStatus = array_get($params, 'identityStatus', 0);
        $identityStartDate = array_get($params, 'identityStartDate', '');
        $identityEndDate = array_get($params, 'identityEndDate', '');

        $query = Bizer::query()->with('bizerIdentityAuditRecord');
        if ($id) {
            $query->where('id', $id);
        }
        if ($mobile) {
            $query->where('mobile', 'like', "%$mobile%");
        }
        if ($name) {
            $query->where('name', 'like', "%$name%");
        }
        if ($bizerStartDate && $bizerEndDate) {
            $query->whereBetween('created_at', [$bizerStartDate, $bizerEndDate]);
        }elseif ($bizerStartDate) {
            $query->where('created_at', '>', $bizerStartDate);
        }elseif ($bizerEndDate) {
            $query->where('created_at', '<', $bizerEndDate);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if (($identityStatus && !empty($identityStatus)) || $identityStartDate || $identityEndDate) {
            $query->whereHas('bizerIdentityAuditRecord', function (Builder $query) use ($identityStatus, $identityStartDate, $identityEndDate, $identityName) {
                if ($identityName) {
                    $query->where('name', 'like', "%$identityName%");
                }
                if ($identityStatus) {
                    if (is_array($identityStatus) && !empty($identityStatus)) {
                        $query->whereIn('status', $identityStatus);
                    } elseif (!is_array($identityStatus)) {
                        $query->where('status', $identityStatus);
                    }
                }
                if ($identityStartDate && $identityEndDate) {
                    $query->whereBetween('created_at', [$identityStartDate, $identityEndDate]);
                } elseif ($identityStartDate) {
                    $query->where('created_at', '>', $identityStartDate);
                } elseif ($identityEndDate) {
                    $query->where('created_at', '<', $identityEndDate);
                }
            });
            if ($identityStatus == BizerIdentityAuditRecord::STATUS_NOT_SUBMIT || (is_array($identityStatus) && in_array(BizerIdentityAuditRecord::STATUS_NOT_SUBMIT, $identityStatus))) {
                $query->orDoesntHave('bizerIdentityAuditRecord');
            }
        }
        $query->orderBy('id', 'desc');

        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            return $data;
        }
    }

    /**
     * 获取业务员详情
     * @param $bizerId
     * @return Bizer
     */
    public static function getBizerDetail($bizerId)
    {
        $bizer = Bizer::find($bizerId);
        if (empty($bizer)) {
            throw new BaseResponseException('该业务员不存在');
        }
        $bizerIdentityAuditRecord = BizerService::getBizerIdentityAuditRecordByBizerId($bizerId);
        $bizer->bizerIdentityAuditRecord = $bizerIdentityAuditRecord;

        $operBizersQuery = OperBizerService::getBizerOper(['bizer_id' => $bizerId], true);
        $operBizers = $operBizersQuery->get();
        $operBizers->each(function ($item) {
            $item->operName = OperService::getNameById($item->oper_id);
        });
        $bizer->operBizers = $operBizers;

        return $bizer;
    }

    /**
     * 更改业务员的状态
     * @param $bizerId
     * @return Bizer
     */
    public static function changeStatus($bizerId)
    {
        $bizer = Bizer::find($bizerId);
        if (empty($bizer)) {
            throw new BaseResponseException('该业务员不存在');
        }
        $bizer->status = $bizer->status == Bizer::STATUS_ON ? Bizer::STATUS_OFF : Bizer::STATUS_ON;
        $bizer->save();

        return $bizer;
    }

    /**
     * 审核业务员身份
     * @param $bizerId
     * @param $status
     * @param $reason
     * @param $user
     * @return BizerIdentityAuditRecord
     */
    public static function identityAudit($bizerId, $status, $reason, $user)
    {
        $identityAuditRecord = self::getBizerIdentityAuditRecordByBizerId($bizerId);
        if (empty($identityAuditRecord)) {
            throw new BaseResponseException('该业务员身份审核信息不存在');
        }
        $identityAuditRecord->status = $status;
        $identityAuditRecord->reason = $reason;
        $identityAuditRecord->update_user = $user->id;
        $identityAuditRecord->save();

        return $identityAuditRecord;
    }

    /**
     * 根据相关参数获取业务员相应列的数组
     * @param $params
     * @param $filed
     * @return \Illuminate\Support\Collection
     */
    public static function getBizerColumnArrayByParams($params, $filed)
    {
        $bizerMobile = array_get($params, 'bizerMobile');
        $bizerName = array_get($params, 'bizerName');

        $query = Bizer::query();
        if ($bizerName) {
            $query->where('name', 'like', "%$bizerName%");
        }
        if ($bizerMobile) {
            $query->where('mobile', 'like', "%$bizerMobile%");
        }
        $arr = $query->select($filed)->get()->pluck($filed);

        return $arr;
    }

    /**
     * 通过业务员手机号码或姓名获取业务员列表
     * @param $bizerNameOrMobile
     * @param int $operId
     * @return \Illuminate\Support\Collection|string
     */
    public static function getBizersByNameOrMobile($bizerNameOrMobile, $operId = 0)
    {
        $ids = [];
        if ($operId) {
            $ids = OperBizerService::getAllbizer(['oper_ids' => $operId])->pluck('bizer_id');
        }
        $bizerIds = Bizer::when(!empty($ids), function (Builder $query) use ($ids) {
                $query->whereIn('id', $ids);
            })
            ->where(function (Builder $query) use ($bizerNameOrMobile) {
                $query->where('name', 'like', "%$bizerNameOrMobile%")
                    ->orWhere('mobile', 'like', "%$bizerNameOrMobile%");
            })
            ->get();

        return $bizerIds;
    }

    /**
     * 根据运营中心ID获取全部业务员列表
     * @param $operId
     * @param array $fields
     * @return Bizer[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllByOperId($operId, $fields = ['*'])
    {
        $list = Bizer::whereHas('operBizer', function($query) use ($operId){
            $query->where('oper_id', $operId);
        })->get();
        return $list;
    }

    /**
     * 修改业务员昵称
     * @param $id
     * @param $name
     * @return Bizer
     */
    public static function changeName($id, $name)
    {
        $bizer = Bizer::find($id);
        if (empty($bizer)) {
            throw new BaseResponseException('该业务员不存在');
        }
        $bizer->name = $name;
        $bizer->save();

        return $bizer;
    }
}