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
     * @return InviteUserChangeBindRecord
     */
    public static function createChangeBindRecord($inviteChannel, $mobile, $changeBindNumber, $currentUser)
    {
        $inviteUserChangeBindRecord = new InviteUserChangeBindRecord();
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
     * @return InviteUserChangeBindRecord
     */
    public static function updateChangeBindNumber($id, $number)
    {
        $record = InviteUserChangeBindRecord::find($id);
        $record->change_bind_number = $number;
        $record->save();
        return $record;
    }

    /**
     * 获取换绑记录列表
     * @param array $param
     * @param int $pageSize
     * @param bool $withQuery
     * @return InviteUserChangeBindRecord|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getChangeBindRecordList($param = [], $pageSize = 15, $withQuery = false)
    {
        $operName = array_get($param, 'operName');
        $inviteChannelName = array_get($param, 'inviteChannelName');

        $query = InviteUserChangeBindRecord::when($operName, function (Builder $query) use ($operName) {
                $query->where('oper_name', 'like', "%$operName%");
            })
            ->when($inviteChannelName, function (Builder $query) use ($inviteChannelName) {
                $query->where('invite_channel_name', 'like', "%$inviteChannelName%");
            })
            ->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            return $data;
        }
    }
}