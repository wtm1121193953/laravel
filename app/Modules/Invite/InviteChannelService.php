<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/26
 * Time: 15:45
 */

namespace App\Modules\Invite;


use App\BaseService;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Cs\CsMerchant;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\User\User;
use App\Modules\Wechat\MiniprogramScene;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Support\Utils;
use function foo\func;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InviteChannelService extends BaseService
{

    /**
     * @param $id
     * @return InviteChannel
     */
    public static function getById($id)
    {
        return InviteChannel::find($id);
    }

    /**
     * 获取运营中心的邀请渠道列表
     * @param $operId
     * @param string $keyword
     * @param bool $getWithQuery
     * @param array $param
     * @return InviteChannel|array
     */
    public static function getOperInviteChannelsByOperId($operId, $keyword = '', $getWithQuery = false, $param = [])
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
            $page = $param['page'] ?: 1;
            $pageSize = $param['pageSize'] ?: 15;
            $orderColumn = $param['orderColumn'];
            $orderType = $param['orderType'];

            $total = $query->count();
            $data = $query->get();

            if ($orderType == 'descending') {
                $data = $data->sortBy($orderColumn);
            } elseif ($orderType == 'ascending') {
                $data = $data->sortByDesc($orderColumn);
            }

            $data = $data->forPage($page,$pageSize)->values()->all();

            return [
                'data' => $data,
                'total' => $total,
            ];
        }
    }

    /**
     * 根据运营中心ID, originId 以及originType获取邀请渠道 (不存在时创建)
     * @param $originId int 邀请人ID
     * @param $originType int 邀请人类型 1-用户 2-商户 3-运营中心
     * @param $operId int 运营中心ID, 存在时则生成对应运营中心的小程序码
     * @return InviteChannel
     */
    public static function getByOriginInfo($originId, $originType, $operId=0)
    {
        $inviteChannel = InviteChannel::where('origin_id', $originId)
            ->where('oper_id', $operId)
            ->where('origin_type', $originType)
            ->first();
        if(empty($inviteChannel)){
            $inviteChannel = self::createInviteChannel($originId, $originType, $operId);
        }
        return $inviteChannel;
    }

    /**
     * 根据场景ID获取邀请渠道
     * @param int $sceneId
     * @return InviteChannel
     */
    public static function getBySceneId($sceneId)
    {
        $scene = MiniprogramSceneService::getById($sceneId);
        if(empty($scene)){
            throw new DataNotFoundException('场景信息不存在');
        }
        if($scene->invite_channel_id <= 0){
            throw new ParamInvalidException('该场景不是邀请渠道的场景');
        }
        $inviteChannel = InviteChannelService::getById($scene->invite_channel_id);
        if(empty($inviteChannel)){
            throw new DataNotFoundException('场景渠道不存在');
        }
        return $inviteChannel;
    }

    /**
     * 根据邀请渠道获取邀请者名称
     * @param InviteChannel $inviteChannel
     * @return mixed|string
     */
    public static function getInviteChannelOriginName(InviteChannel $inviteChannel)
    {
        $originType = $inviteChannel->origin_type;
        $originId = $inviteChannel->origin_id;

        $originName = '';
        if($originType == InviteChannel::ORIGIN_TYPE_USER){
            $user = User::findOrFail($originId);
            $originName = Utils::getHalfHideMobile($user->mobile);
        }else if($originType == InviteChannel::ORIGIN_TYPE_MERCHANT){
            $originName = Merchant::where('id', $originId)->value('signboard_name');
        }else if($originType == InviteChannel::ORIGIN_TYPE_OPER){
            $originName = Oper::where('id', $originId)->value('name');
        }else if($originType == InviteChannel::ORIGIN_TYPE_CS_MERCHANT){
            $originName = CsMerchant::where('id', $originId)->value('name');
        }
        return $originName;
    }

    /**
     * 生成推广渠道
     * @param $originId int 邀请人ID
     * @param $originType int 邀请人类型 1-用户 2-商户 3-运营中心
     * @param $operId int 运营中心ID, 存在时则生成对应运营中心的小程序码
     * @return InviteChannel
     */
    public static function createInviteChannel($originId, $originType, $operId=0)
    {
        // 不能重复生成
        $inviteChannel = InviteChannel::where('oper_id', $operId)
            ->where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        if($inviteChannel) {
            return $inviteChannel;
        }

        $inviteChannel = new InviteChannel();
        $inviteChannel->oper_id = $operId;
        $inviteChannel->origin_id = $originId;
        $inviteChannel->origin_type = $originType;
        $inviteChannel->save();

//        if($operId > 0){
            // 邀请渠道创建成功后添加小程序场景
            MiniprogramSceneService::createInviteScene($inviteChannel);
//        }

        return $inviteChannel;
    }

    /**
     * 添加推广渠道
     * @param $operId
     * @param $name
     * @param $remark
     * @return InviteChannel
     */
    public static function createOperInviteChannel($operId, $name, $remark): InviteChannel
    {
        $exist = InviteChannel::where('name', $name)->where('oper_id', $operId)->first();
        if ($exist){
            throw new ParamInvalidException('渠道名称不能重复');
        }

        $inviteChannel = new InviteChannel();
        $inviteChannel->oper_id = $operId;
        $inviteChannel->origin_id = $operId;
        $inviteChannel->origin_type = InviteChannel::ORIGIN_TYPE_OPER;
        $inviteChannel->name = $name;
        $inviteChannel->remark = $remark;
        $inviteChannel->save();

        // 邀请渠道创建成功后添加小程序场景
        MiniprogramSceneService::createInviteScene($inviteChannel);

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
    public static function updateOperInviteChannel($inviteChannelId, $operId, $name, $remark): InviteChannel
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

    /**
     * 获取全部运营中心的邀请渠道
     * @param array $params
     * @param bool $withQuery
     * @return InviteChannel|InviteChannel[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getInviteChannels(array $params=[], $withQuery = false)
    {
        $operName = array_get($params, 'operName', '');
        $inviteChannelName = array_get($params, 'inviteChannelName', '');
        $originType = array_get($params, 'originType', '');
        $originIds = array_get($params, 'originIds', []);

        $query = InviteChannel::when(!empty($originIds), function (Builder $query) use ($originIds) {
                if (is_array($originIds)) {
                    $query->whereIn('origin_id', $originIds);
                } else {
                    $query->where('origin_id', $originIds);
                }
            })
            ->when($originType, function (Builder $query) use ($originType) {
                $query->where('origin_type', $originType);
            })
            ->when($operName, function (Builder $query) use ($operName) {
                $operIds = Oper::where('name', 'like', "%$operName%")
                    ->select('id')
                    ->get()
                    ->pluck('id');
                $query->whereIn('oper_id', $operIds);
            })
            ->when($inviteChannelName, function (Builder $query) use ($inviteChannelName) {
                $query->where('name', 'like', "%$inviteChannelName%");
            })
            ->withCount('inviteUserRecords')
            ->orderByDesc('id');

        if ($withQuery) {
            return $query;
        } else {
            $data = $query->get();
            return $data;
        }
    }

    /**
     * 获取运营中心的所有邀请渠道
     * @param $oper_id
     * @param bool $ori
     * @return array
     */
    public static function allOperInviteChannel($oper_id, $ori = false)
    {
        $data = InviteChannel::select('id','name')
            ->where('origin_id','=',$oper_id)
            ->where('origin_type','=',3)
            ->orderByDesc('id')->get();

        if ($ori) {
            return $data->toArray();
        }
        $rt = [];
        if ($data) {
            foreach ($data as $v) {
                $rt[$v->id] = $v->name;
            }
        }

        return $rt;

    }

}