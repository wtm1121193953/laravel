<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/12
 * Time: 23:04
 */

namespace App\Modules\Invite;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Jobs\MerchantLevelComputeJob;
use App\Jobs\Schedule\InviteUserStatisticsDailyJob;
use App\Modules\Admin\AdminUser;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\Tps\TpsBind;
use App\Modules\Tps\TpsBindService;
use App\Modules\User\User;
use App\Modules\User\UserService;
use App\ResultCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 用户邀请相关服务
 * Class InviteService
 * @package App\Modules\Invite
 */
class InviteUserService
{

    /**
     * 获取用户上级, 可能是用户/商户或运营中心
     * @param $userId
     * @return Merchant|Oper|User|null
     */
    public static function getParent($userId)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if (empty($inviteRecord)) {
            // 如果没有用户没有上级, 不做任何处理
            return null;
        }
        if ($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT) {
            $object = Merchant::where('id', $inviteRecord->origin_id)->firstOrFail();
        } else if ($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_OPER) {
            $object = Oper::where('id', $inviteRecord->origin_id)->firstOrFail();
        } else {
            $object = User::findOrFail($inviteRecord->origin_id);
        }
        return $object;
    }

    /**
     * 获取上级用户名称
     * @param $userId
     * @return string|null
     */
    public static function getParentName($userId)
    {
        $obj = self::getParent($userId);
        if($obj instanceof User){
            return $obj->mobile;
        }else if($obj instanceof Merchant || $obj instanceof Oper) {
            return $obj->name;
        }else {
            return null;
        }
    }

    /**
     * 绑定邀请人信息到用户
     * @param $userId
     * @param InviteChannel $inviteChannel
     * @throws \Exception
     */
    public static function bindInviter($userId, InviteChannel $inviteChannel)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if ($inviteRecord) {
            // 如果当前用户已被邀请过, 不能重复邀请
            throw new BaseResponseException('您已经被邀请过了, 不能重复接收邀请', ResultCode::USER_ALREADY_BEEN_INVITE);
        }
        if ($inviteChannel->origin_type == InviteChannel::ORIGIN_TYPE_USER) {
            if ($inviteChannel->origin_id == $userId) {
                throw new ParamInvalidException('不能扫描自己的邀请码');
            }
            // 判断用户及上级用户是否都绑定了tps账号
            if (
                TpsBindService::getTpsBindInfoByOriginInfo($userId, TpsBind::ORIGIN_TYPE_USER)
                && TpsBindService::getTpsBindInfoByOriginInfo($inviteChannel->origin_id, TpsBind::ORIGIN_TYPE_USER)
            ) {
                throw new BaseResponseException('您和您的邀请人都已绑定TPS账号, 请尝试其他邀请人');
            }
        }

        $inviteRecord = new InviteUserRecord();
        $inviteRecord->user_id = $userId;
        $inviteRecord->invite_channel_id = $inviteChannel->id;
        $inviteRecord->origin_id = $inviteChannel->origin_id;
        $inviteRecord->origin_type = $inviteChannel->origin_type;

        $inviteRecord->save();

        if ($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT) {
            MerchantLevelComputeJob::dispatch($inviteRecord->origin_id);
        }
    }

    /**
     * 解绑用户邀请关系
     * @param InviteUserRecord $inviteRecord
     * @param int $batchChangedRecordId
     * @throws \Exception
     */
    public static function unbindInviter(InviteUserRecord $inviteRecord, $batchChangedRecordId = 0)
    {
        // 删除邀请记录
        $inviteRecord->delete();

        // 添加解绑记录
        $userId = $inviteRecord->user_id;
        $inviteUserUnbindRecord = new InviteUserUnbindRecord();
        $inviteUserUnbindRecord->user_id = $userId;
        $inviteUserUnbindRecord->status = InviteUserUnbindRecord::STATUS_UNBIND;
        $user = UserService::getUserById($userId);
        if (empty($user)) {
            throw new BaseResponseException('解绑的用户不存在');
        }
        $inviteUserUnbindRecord->mobile = $user->mobile;
        $inviteUserUnbindRecord->batch_record_id = $batchChangedRecordId ?: 0;
        $inviteUserUnbindRecord->old_invite_user_record = $inviteRecord->toJson();
        $inviteUserUnbindRecord->save();

        // 更新用户邀请数量统计
        $date = $inviteRecord->created_at->format('Y-m-d');
        InviteUserStatisticsDailyJob::dispatch(Carbon::createFromFormat('Y-m-d', $date));

    }

    /**
     * 批量换绑
     * @param InviteChannel $oldInviteChannel
     * @param InviteChannel $newInviteChannel 要绑定的新渠道
     * @param AdminUser $operator
     * @param InviteUserRecord[]|\Illuminate\Database\Eloquent\Collection $inviteUserRecords
     * @return InviteUserBatchChangedRecord
     * @throws \Exception
     */
    public static function batchChangeInviter(InviteChannel $oldInviteChannel, InviteChannel $newInviteChannel, AdminUser $operator, $inviteUserRecords=null)
    {
        if(is_null($inviteUserRecords)){
            $inviteUserRecords = self::getInviteRecordsByInviteChannelId($oldInviteChannel->id);
        }

        DB::beginTransaction();
        try{
            //首先操作invite_user_change_bind_records表，写入换绑记录
            $inviteUserBatchChangedRecord = new InviteUserBatchChangedRecord();
            $inviteUserBatchChangedRecord->invite_channel_id = $oldInviteChannel->id;
            $inviteUserBatchChangedRecord->invite_channel_name = $oldInviteChannel->name;
            $inviteUserBatchChangedRecord->invite_channel_remark = $oldInviteChannel->remark;
            $inviteUserBatchChangedRecord->invite_channel_oper_id = $oldInviteChannel->oper_id;
            $operName = OperService::getNameById($oldInviteChannel->oper_id);
            $inviteUserBatchChangedRecord->invite_channel_oper_name = $operName;
            $inviteUserBatchChangedRecord->new_invite_channel_id = $newInviteChannel->id;

            if($newInviteChannel->origin_type == InviteChannel::ORIGIN_TYPE_USER){
                $user = UserService::getUserById($newInviteChannel->origin_id);
                $inviteUserBatchChangedRecord->bind_mobile = $user->mobile;
            }
            $inviteUserBatchChangedRecord->change_bind_number = 0;

            $inviteUserBatchChangedRecord->operator_id = $operator->id;
            $inviteUserBatchChangedRecord->operator = $operator->username;
            $inviteUserBatchChangedRecord->save();

            $changeBindNumber = 0;
            $changeBindErrorNumber = 0;
            $needStatisticsDate = []; //需要统计的日期
            // 循环遍历需换绑的记录，在解绑表invite_user_unbind_records中加入解绑记录;
            // 添加新的邀请记录在invite_user_records中,并删除记录表中的旧记录
            foreach ($inviteUserRecords as $inviteUserRecord) {
                $date = $inviteUserRecord->created_at->format('Y-m-d');
                $needStatisticsDate[$date] = $date;

                try {
                    InviteUserService::changeInviter($inviteUserRecord, $newInviteChannel, $inviteUserBatchChangedRecord->id);
                    $changeBindNumber ++;
                }catch (\Exception $e){
                    $changeBindErrorNumber ++;
                }
            }

            $inviteUserBatchChangedRecord->change_bind_number = $changeBindNumber;
            $inviteUserBatchChangedRecord->change_error_number = $changeBindErrorNumber;
            $inviteUserBatchChangedRecord->save();

            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

        // 记录换绑完成之后，对每日统计表invite_user_statistics_dailies进行更改
        if (!empty($needStatisticsDate)) {
            foreach ($needStatisticsDate as $date) {
                InviteUserStatisticsDailyJob::dispatch(Carbon::createFromFormat('Y-m-d', $date));
            }
        }

        return $inviteUserBatchChangedRecord;
    }

    /**
     * 换绑邀请人
     * @param InviteUserRecord $inviteUserRecord
     * @param InviteChannel $inviteChannel
     * @param int $inviteUserBatchChangedRecordId 换绑批次ID
     * @throws \Exception
     */
    public static function changeInviter(InviteUserRecord $inviteUserRecord, InviteChannel $inviteChannel, $inviteUserBatchChangedRecordId)
    {

        $userId = $inviteUserRecord->user_id;

        // 判断是否可以邀请
        if ($inviteChannel->origin_type == InviteChannel::ORIGIN_TYPE_USER) {
            if ($inviteChannel->origin_id == $userId) {
                throw new ParamInvalidException('不能自己绑定自己哦');
            }
            // 判断用户及上级用户是否都绑定了tps账号
            if (
                TpsBindService::getTpsBindInfoByOriginInfo($userId, TpsBind::ORIGIN_TYPE_USER)
                && TpsBindService::getTpsBindInfoByOriginInfo($inviteChannel->origin_id, TpsBind::ORIGIN_TYPE_USER)
            ) {
                throw new BaseResponseException('您和您的邀请人都已绑定TPS账号, 请尝试其他邀请人');
            }
        }

        DB::beginTransaction();
        try {

            // 解绑旧的邀请关系
            self::unbindInviter($inviteUserRecord, $inviteUserBatchChangedRecordId);

            // 保存新的邀请记录
            $newInviteRecord = new InviteUserRecord();
            $newInviteRecord->user_id = $userId;
            $newInviteRecord->invite_channel_id = $inviteChannel->id;
            $newInviteRecord->origin_id = $inviteChannel->origin_id;
            $newInviteRecord->origin_type = $inviteChannel->origin_type;
            $newInviteRecord->created_at = $inviteUserRecord->created_at;
            $newInviteRecord->save();

            if ($newInviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT) {
                MerchantLevelComputeJob::dispatch($newInviteRecord->origin_id);
            }
            if ($inviteUserRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT) {
                MerchantLevelComputeJob::dispatch($inviteUserRecord->origin_id);
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw new $e;
        }
    }

    /**
     * 获取指定邀请渠道的邀请记录
     * @param $inviteChannelId
     * @param $params
     * @param bool $getWithQuery
     * @return LengthAwarePaginator|Builder
     */
    public static function getInviteRecordsByInviteChannelId($inviteChannelId, $params = [], $getWithQuery = false)
    {
        $mobile = array_get($params, 'mobile');
        $startTime = array_get($params, 'startTime');
        $endTime = array_get($params, 'endTime');

        $query = InviteUserRecord::where('invite_channel_id', $inviteChannelId)
            ->whereHas('user', function (Builder $query) use ($mobile, $startTime, $endTime) {
                $query
                    ->when($mobile, function (Builder $query) use ($mobile) {
                        $query->where('mobile', 'like', "%$mobile%");
                    })
                    ->when($startTime && $endTime, function (Builder $query) use ($startTime, $endTime) {
                        $query->whereBetween('created_at', [$startTime, $endTime]);
                    })
                    ->when($startTime && !$endTime, function (Builder $query) use ($startTime) {
                        $query->where('created_at', '>=', $startTime);
                    })
                    ->when($endTime && !$startTime, function (Builder $query) use ($endTime) {
                        $query->where('created_at', '<=', $endTime);
                    });
            })
            ->with('user:id,mobile,created_at')
            ->orderByDesc('user_id');
        if ($getWithQuery) {
            return $query;
        }
        $data = $query->paginate();
        return $data;
    }

    /**
     * 根据用户ID查询用户的邀请记录
     * @param $userId
     * @return InviteUserRecord
     */
    public static function getInviteRecordByUserId($userId)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        return $inviteRecord;
    }

    /**
     * 根据邀请人信息查询邀请记录
     * @param $originId
     * @param $originType
     * @return InviteUserRecord[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getInviteRecordsByOriginInfo($originId, $originType)
    {
        $list = InviteUserRecord::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->get();
        return $list;
    }

    /**
     * 通过id获取邀请用户记录
     * @param $ids
     * @return InviteUserRecord|InviteUserRecord[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getInviteRecordsByIds($ids)
    {
        if (is_array($ids)) {
            $data = InviteUserRecord::whereIn('id', $ids)->get();
        } else {
            $data = InviteUserRecord::find($ids);
        }

        return $data;
    }

    /**
     * 根据批次ID获取解绑记录列表
     * @param $batchRecordId
     * @param int $pageSize
     * @param bool $withQuery
     * @return InviteUserUnbindRecord|LengthAwarePaginator
     */
    public static function getUnbindRecordsByBatchId($batchRecordId, $pageSize = 15, $withQuery = false)
    {
        $query = InviteUserUnbindRecord::when($batchRecordId, function (Builder $query) use ($batchRecordId) {
            $query->where('batch_record_id', $batchRecordId);
        })
            ->orderBy('id', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function ($item) {
                $item->old_invite_user_record = json_decode($item->old_invite_user_record, true);
            });
            return $data;
        }
    }

    /**
     * 获取批量换绑记录
     * @param array $param
     * @param int $pageSize
     * @param bool $withQuery
     * @return InviteUserBatchChangedRecord|LengthAwarePaginator
     */
    public static function getBatchChangedRecords($param = [], $pageSize = 15, $withQuery = false)
    {
        $operName = array_get($param, 'operName');
        $inviteChannelName = array_get($param, 'inviteChannelName');

        $query = InviteUserBatchChangedRecord::when($operName, function (Builder $query) use ($operName) {
            $query->where('invite_channel_oper_name', 'like', "%$operName%");
        })
            ->when($inviteChannelName, function (Builder $query) use ($inviteChannelName) {
                $query->where('invite_channel_name', 'like', "%$inviteChannelName%");
            })
            ->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function ($item) {
                $inviteChannel = InviteChannelService::getById($item->invite_channel_id);
                $item->invite_channel_name = $inviteChannel->name;
                $item->invite_channel_remark = $inviteChannel->remark;
            });
            return $data;
        }
    }
}