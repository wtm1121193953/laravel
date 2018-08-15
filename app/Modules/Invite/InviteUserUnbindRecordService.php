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

class InviteUserUnbindRecordService extends BaseService
{

    /**
     * 创建用户解绑记录
     * @param $userId
     * @param $status
     * @param int $changeBindRecordId
     * @param InviteUserRecord|null $inviteUserRecord
     * @return InviteUserUnbindRecord
     */
    public static function createUnbindRecord($userId, $status, $changeBindRecordId = 0, InviteUserRecord $inviteUserRecord = null)
    {
        $inviteUserUnbindRecord = new InviteUserUnbindRecord();
        $inviteUserUnbindRecord->user_id = $userId;
        $inviteUserUnbindRecord->status = $status;
        $user = UserService::getUserById($userId);
        if (empty($user)) {
            throw new BaseResponseException('解绑的用户不存在');
        }
        $inviteUserUnbindRecord->mobile = $user->mobile;
        $inviteUserUnbindRecord->change_bind_record_id = $changeBindRecordId ?: 0;
        $inviteUserUnbindRecord->old_invite_user_record = $inviteUserRecord ? json_encode($inviteUserRecord->toArray()) : '';
        $inviteUserUnbindRecord->save();

        return $inviteUserUnbindRecord;
    }
}