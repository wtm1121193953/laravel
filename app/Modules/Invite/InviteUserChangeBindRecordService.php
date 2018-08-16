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
use Illuminate\Database\Eloquent\Builder;

class InviteUserChangeBindRecordService extends BaseService
{

    /**
     * 创建用户换绑记录
     * @param $inviteChannel
     * @param $mobile
     * @param $changeBindNumber
     * @param $currentUser
     * @return InviteUserBatchChangedRecord
     */
    public static function createChangeBindRecord($inviteChannel, $mobile, $changeBindNumber, $currentUser)
    {
        $inviteUserChangeBindRecord = new InviteUserBatchChangedRecord();
        $inviteUserChangeBindRecord->invite_channel_name = $inviteChannel->name;
        $inviteUserChangeBindRecord->remark = $inviteChannel->remark;
        $inviteUserChangeBindRecord->oper_id = $inviteChannel->oper_id;
        $oper = OperService::getById($inviteChannel->oper_id, 'name');
        $inviteUserChangeBindRecord->oper_name = $oper->name;
        $inviteUserChangeBindRecord->change_bind_number = $changeBindNumber;
        $inviteUserChangeBindRecord->bind_mobile = $mobile;
        $inviteUserChangeBindRecord->operator_id = $currentUser->id;
        $inviteUserChangeBindRecord->operator = $currentUser->username;
        $inviteUserChangeBindRecord->save();

        return $inviteUserChangeBindRecord;
    }

    /**
     * 更新换绑记录中的换绑用户数量
     * @param $id
     * @param $number
     * @return InviteUserBatchChangedRecord
     */
    public static function updateChangeBindNumber($id, $number)
    {
        $record = InviteUserBatchChangedRecord::find($id);
        $record->change_bind_number = $number;
        $record->save();
        return $record;
    }

}