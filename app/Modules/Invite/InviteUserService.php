<?php

namespace App\Modules\Invite;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Jobs\InviteStatisticsDailyUpdateByOriginInfoAndDate;
use App\Jobs\MerchantLevelComputeJob;
use App\Jobs\UpdateMarketingStatisticsInviteInfo;
use App\Modules\Admin\AdminUser;
use App\Modules\Cs\CsMerchant;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\User\User;
use App\Modules\User\UserService;
use App\ResultCode;
use App\Support\Utils;
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
        } else if ($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_CS) {
            $object = CsMerchant::where('id', $inviteRecord->origin_id)->firstOrFail();
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
        }else if($obj instanceof Merchant) {
            return $obj->signboard_name;
        }else if($obj instanceof CsMerchant) {
            return $obj->signboard_name;
        }else if($obj instanceof Oper){
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
            /*if (
                TpsBindService::getTpsBindInfoByOriginInfo($userId, TpsBind::ORIGIN_TYPE_USER)
                && TpsBindService::getTpsBindInfoByOriginInfo($inviteChannel->origin_id, TpsBind::ORIGIN_TYPE_USER)
            ) {
                throw new BaseResponseException('您和您的邀请人都已绑定TPS账号, 请尝试其他邀请人');
            }*/
        }
        $inviteChannelParent = self::getParent($inviteChannel->origin_id);
        if ($inviteChannelParent && $inviteChannelParent instanceof User && $inviteChannelParent->id == $userId) {
            $name = InviteChannelService::getInviteChannelOriginName($inviteChannel);
            throw new BaseResponseException("您的推荐人是{$name}，您不能在接受{$name}的分享");
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
     * @return InviteUserUnbindRecord
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

        return $inviteUserUnbindRecord;
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
            $operName = OperService::getNameById($oldInviteChannel->oper_id) ?: '';
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
                InviteStatisticsDailyUpdateByOriginInfoAndDate::dispatch($newInviteChannel->origin_id, $newInviteChannel->origin_type, Carbon::createFromFormat('Y-m-d', $date));
                InviteStatisticsDailyUpdateByOriginInfoAndDate::dispatch($oldInviteChannel->origin_id, $oldInviteChannel->origin_type, Carbon::createFromFormat('Y-m-d', $date));
                // 换绑后 营销统计更新
                UpdateMarketingStatisticsInviteInfo::dispatch($newInviteChannel->origin_id, $newInviteChannel->origin_type, $date);
                UpdateMarketingStatisticsInviteInfo::dispatch($oldInviteChannel->origin_id, $oldInviteChannel->origin_type, $date);
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
            /*if (
                TpsBindService::getTpsBindInfoByOriginInfo($userId, TpsBind::ORIGIN_TYPE_USER)
                && TpsBindService::getTpsBindInfoByOriginInfo($inviteChannel->origin_id, TpsBind::ORIGIN_TYPE_USER)
            ) {
                throw new BaseResponseException('您和您的邀请人都已绑定TPS账号, 请尝试其他邀请人');
            }*/
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


    /**
     * 根据月份获取当月的邀请记录
     * @param $userId
     * @param $month
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getInviteUsersByMonthAndUserId($userId, $month, $pageSize = 20)
    {
        return self::getInviteUsersByMonthAndOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER, $month, $pageSize);
    }

    /**
     * 根据月份以及邀请人信息获取邀请的用户列表
     * @param $originId
     * @param $originType
     * @param $month
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getInviteUsersByMonthAndOriginInfo($originId, $originType, $month, $pageSize = 15)
    {
        $firstDay = date('Y-m-01 00:00:00', strtotime($month));
        $lastDay = date('Y-m-d 23:59:59', strtotime("$firstDay + 1 month - 1 day"));
        $inviteUserRecords = InviteUserRecord::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->whereBetween('created_at', [$firstDay, $lastDay])
            ->orderBy('created_at', 'desc')
            ->with('user:id,mobile')
            ->paginate($pageSize);
        $inviteUserRecords->each(function(InviteUserRecord $item){
            $item->user_mobile = Utils::getHalfHideMobile($item->user->mobile);
        });
        return $inviteUserRecords;
    }

    /**
     * 获取用户邀请记录, 并根据月份分组
     * @param $userId
     * @param int $pageSize
     * @return array
     */
    public static function getInviteUsersGroupByMonthForUser($userId, $pageSize = 20)
    {
        $inviteUserRecords = InviteUserRecord::where('origin_id', $userId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
            ->orderBy('created_at', 'desc')
            ->with('user:id,mobile')
            ->simplePaginate($pageSize);
        $list = collect($inviteUserRecords->items());
        $data = $list->each(function (InviteUserRecord $item){
            $item->user_mobile = isset($item->user->mobile) ? Utils::getHalfHideMobile($item->user->mobile) : '';
            $item->created_month = $item->created_at->format('Y-m');
        })
            ->groupBy('created_month')
            ->map(function($item, $key) use ($userId){
                $firstDay = date('Y-m-01 00:00:00', strtotime($key));
                $lastDay = date('Y-m-d 23:59:59', strtotime("$key + 1 month - 1 day"));
                $count = InviteUserRecord::where('origin_id', $userId)
                    ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
                    ->whereBetween('created_at', [$firstDay, $lastDay])
                    ->count();

                return [
                    'sub' => $item,
                    'count' => $count,
                ];
            });

        return $data;
    }

    /**
     * 根据渠道信息获取渠道邀请的用户列表
     * @param $originId
     * @param $originType
     * @param $params
     * @param bool $withQuery
     * @return User|LengthAwarePaginator
     */
    public static function getInviteUsersByOriginInfo($originId, $originType, $params = [], $withQuery = false)
    {
        $mobile = array_get($params, 'mobile');
        $userIds = InviteUserRecord::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->select('user_id')
            ->get()
            ->pluck('user_id');
        $query = User::whereIn('id', $userIds)
            ->when($mobile, function (Builder $query) use ($mobile) {
                $query->where('mobile', 'like', "%$mobile%");
            });

        if($withQuery) return $query;

        $pageSize = array_get($params, 'pageSize', 15);
        $orderColumn = array_get($params, 'orderColumn', 'id') ?: 'id';
        $orderType = array_get($params, 'orderType', 'descending') ?: 'descending';
        $orderType = $orderType == 'descending' ? 'desc' : 'asc';

          $data = $query->orderBy($orderColumn, $orderType)
                ->paginate($pageSize);

          $data->each(function ($item){
                $item->mobile = Utils::getHalfHideMobile($item->mobile);
            });
        return $data;
    }

    /**
     * 查询商户邀请用户的列表
     * @param $merchantId
     * @param array $params
     * @param bool $withQuery
     * @return User|LengthAwarePaginator
     */
    public static function getInviteUsersWithOrderCountByMerchantId($merchantId, $params = [], $withQuery = false)
    {
        return self::getInviteUsersByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT, $params, $withQuery);
    }

    /**
     * 查询超市商户邀请用户的列表
     * @param $csMerchantId
     * @param array $params
     * @param bool $withQuery
     * @return User|LengthAwarePaginator
     */
    public static function getInviteUsersWithOrderCountByCsMerchantId($csMerchantId, $params = [], $withQuery = false)
    {
        return self::getInviteUsersByOriginInfo($csMerchantId, InviteChannel::ORIGIN_TYPE_CS_MERCHANT, $params, $withQuery);
    }

    /**
     * 获取运营中心邀请的用户列表
     * @param $params
     * @param bool $return_query
     * @return LengthAwarePaginator|\Illuminate\Database\Query\Builder|mixed
     */
    public static function operInviteList($params,bool $return_query = false)
    {

        $query = DB::table('invite_user_records')
            ->select('invite_user_records.*','users.mobile','users.wx_nick_name','users.order_count','users.created_at as user_created_at')
            ->leftJoin('users', 'invite_user_records.user_id', '=', 'users.id')
            ->where('invite_user_records.origin_id','=',$params['origin_id'])
            ->where('invite_user_records.origin_type','=',3)
            ->when($params['mobile'],function ( $query) use ($params){
                $query->where('users.mobile', $params['mobile']);
            })
            ->when($params['invite_channel_id'], function ( $query) use ($params){
                $query->where('invite_user_records.invite_channel_id', $params['invite_channel_id']);
            })
            ->when(!empty($params['orderColumn']) && !empty($params['orderType']), function ( $query) use ($params){
                $sort = $params['orderType'] == 'ascending'?'asc':'desc';
                $query->orderBy($params['orderColumn'],$sort);
            }, function ($query) {
                $query->orderBy('users.created_at','desc');
            })
        ;


        if ($return_query) {
            return $query;
        }

        $data = $query->paginate();

        if ($data) {
            $channels = InviteChannelService::allOperInviteChannel($params['origin_id']);

            $data->each(function ($item) use ($channels){
               $item->invite_channel_name = $channels[$item->invite_channel_id];
               $item->mobile = Utils::getHalfHideMobile($item->mobile);
            });
        }

        return $data;

    }

    /**
     * 获取邀请记录列表
     * @param $params
     * @param bool $withQuery
     * @return LengthAwarePaginator|Builder
     */
    public static function getInviteRecordList($params, $withQuery = false)
    {
        $originId = array_get($params, 'originId');
        $originType = array_get($params, 'originType');
        $startDate = array_get($params, 'startDate');
        $endDate = array_get($params, 'endDate');

        $query = InviteUserRecord::query()->with('user');
        if ($originId) {
            $query->where('origin_id', $originId);
        }
        if ($originType) {
            $query->where('origin_type', $originType);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        $query->orderBy('created_at', 'desc');

        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate();
            return $data;
        }
    }
}