<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 19:51
 */

namespace App\Modules\Invite;


use App\BaseService;
use App\Modules\Oper\OperService;

class InviteUserChangeBindRecordService extends BaseService
{

    /**
     * 创建用户换绑记录
     * @param $inviteChannel
     * @param $mobile
     * @param $changeBindNumber
     * @param $currentUser
     * @return InviteUserChangeBindRecord
     */
    public static function createChangeBindRecord($inviteChannel, $mobile, $changeBindNumber, $currentUser)
    {
        $inviteUserChangeBindRecord = new InviteUserChangeBindRecord();
        $inviteUserChangeBindRecord->invite_channel_name = $inviteChannel->name;
        $inviteUserChangeBindRecord->remark = $inviteChannel->remark;
        $inviteUserChangeBindRecord->oper_id = $inviteChannel->oper_id;
        $oper = OperService::detail($inviteChannel->oper_id);
        $inviteUserChangeBindRecord->oper_name = $oper->name;
        $inviteUserChangeBindRecord->change_bind_number = $changeBindNumber;
        $inviteUserChangeBindRecord->bind_mobile = $mobile;
        $inviteUserChangeBindRecord->operator_id = $currentUser->id;
        $inviteUserChangeBindRecord->operator = $currentUser->username;
        $inviteUserChangeBindRecord->save();

        return $inviteUserChangeBindRecord;
    }
}