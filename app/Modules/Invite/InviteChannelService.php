<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/26
 * Time: 15:45
 */

namespace App\Modules\Invite;


use App\BaseService;
use App\Exceptions\NoPermissionException;
use App\Exceptions\ParamInvalidException;
use Illuminate\Database\Eloquent\Builder;

class InviteChannelService extends BaseService
{
    /**
     * 获取邀请渠道列表
     * @param $operId
     * @param string $keyword
     * @param bool $getWithQuery
     * @return InviteChannel|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($operId, $keyword = '', $getWithQuery = false)
    {
        $query = InviteChannel::where('origin_id', $operId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_OPER)
            ->when('keyword', function (Builder $query) use ($keyword){
                $query->where('name', 'like', "%$keyword%");
            })
            ->withCount('inviteUserRecords')
            ->orderByDesc('id');
        if ($getWithQuery) {
            return $query;
        } else {
            $data = $query->paginate();
            return $data;
        }
    }

    /**
     * 添加推广渠道
     * @param $operId
     * @param $originId
     * @param $originType
     * @param $name
     * @param $remark
     * @return InviteChannel
     */
    public static function add($operId, $originId, $originType, $name, $remark): InviteChannel
    {
        $exist = InviteChannel::where('name', $name)->where('oper_id', $operId)->first();
        if ($exist){
            throw new ParamInvalidException('渠道名称不能重复');
        }

        $inviteChannel = new InviteChannel();
        $inviteChannel->oper_id = $operId;
        $inviteChannel->origin_id = $originId;
        $inviteChannel->origin_type = $originType;
        $inviteChannel->name = $name;
        $inviteChannel->remark = $remark;

        return $inviteChannel;
    }

    /**
     * 编辑邀请渠道
     * @param $inviteChannelId
     * @param $operId
     * @param $name
     * @param $remark
     * @return InviteChannel
     */
    public static function edit($inviteChannelId, $operId, $name, $remark): InviteChannel
    {
        $exist = InviteChannel::where('name', $name)
            ->where('id', '<>', $inviteChannelId)
            ->where('oper_id', $operId)
            ->first();
        if ($exist){
            throw new ParamInvalidException('渠道名称不能重复');
        }

        $inviteChannel = InviteChannel::find($inviteChannelId);
        if (empty($inviteChannel)) {
            throw new ParamInvalidException('邀请渠道不存在');
        }

        if($inviteChannel->origin_id != $operId){
            throw new NoPermissionException('无权限修改');
        }

        $inviteChannel->name = $name;
        $inviteChannel->remark = $remark;
        $inviteChannel->save();

        return $inviteChannel;
    }
}