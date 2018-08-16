<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 20:28
 */

namespace App\Modules\Invite;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\User\UserService;
use Illuminate\Database\Eloquent\Builder;

class InviteUserUnbindRecordService extends BaseService
{

    /**
     * 创建用户解绑记录
     * @param $userId
     * @param $status
     * @param int $batchRecordId
     * @param InviteUserRecord|null $inviteUserRecord
     * @return InviteUserUnbindRecord
     */
    public static function createUnbindRecord($userId, $status, $batchRecordId = 0, InviteUserRecord $inviteUserRecord = null)
    {
        $inviteUserUnbindRecord = new InviteUserUnbindRecord();
        $inviteUserUnbindRecord->user_id = $userId;
        $inviteUserUnbindRecord->status = $status;
        $user = UserService::getUserById($userId);
        if (empty($user)) {
            throw new BaseResponseException('解绑的用户不存在');
        }
        $inviteUserUnbindRecord->mobile = $user->mobile;
        $inviteUserUnbindRecord->batch_record_id = $batchRecordId ?: 0;
        $inviteUserUnbindRecord->old_invite_user_record = $inviteUserRecord ? json_encode($inviteUserRecord->toArray()) : '';
        $inviteUserUnbindRecord->save();

        return $inviteUserUnbindRecord;
    }

    /**
     * 获取解绑记录
     * @param array $param
     * @param int $pageSize
     * @param bool $withQuery
     * @return InviteUserUnbindRecord|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getUnbindRecordList($param = [], $pageSize = 15, $withQuery = false)
    {
        $batchRecordId = array_get($param, 'batchRecordId');
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
}