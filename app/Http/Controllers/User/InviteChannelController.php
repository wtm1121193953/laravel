<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\User;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteService;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Result;

class InviteChannelController extends Controller
{

    /**
     *
     */
    public function getInviteQrcode()
    {
        $operId = request()->get('current_oper')->id;
        $userId = request()->get('current_user')->id;
        $inviteChannel = InviteChannelService::getByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER, $operId);
        $inviteChannel->origin_name = InviteChannelService::getInviteChannelOriginName($inviteChannel);
        $scene = MiniprogramSceneService::getByInviteChannel($inviteChannel);
        $url = MiniprogramSceneService::getMiniprogramAppCode($scene);

        return Result::success([
            'qrcode_url' => $url,
            'inviteChannel' => $inviteChannel
        ]);
    }

    /**
     * 根据场景ID获取邀请人信息
     */
    public function getInviterBySceneId()
    {
        $sceneId = request('sceneId');
        if(empty($sceneId)){
            throw new ParamInvalidException('场景ID不能为空');
        }
        // 判断场景类型必须是 推广注册小程序码 才可以
        $inviteChannel = InviteChannelService::getBySceneId($sceneId);

        if($inviteChannel->origin_type == InviteChannel::ORIGIN_TYPE_USER){
            throw new ParamInvalidException('会员二维码已经失效');
        }

        $inviteChannel->origin_name = InviteChannelService::getInviteChannelOriginName($inviteChannel);
        return Result::success($inviteChannel);
    }

    /**
     * 绑定推荐人
     */
    public function bindInviter()
    {
        $inviteChannelId = request('inviteChannelId');
        $inviteChannel = InviteChannelService::getById($inviteChannelId);
        if(empty($inviteChannel)){
            throw new ParamInvalidException('邀请渠道不存在');
        }
        InviteService::bindInviter(request()->get('current_user')->id, $inviteChannel);
        return Result::success();
    }

    /**
     * 用户分享列表统计接口
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getInviteUserStatisticsByUserId()
    {
        $userId = request('userId');
        $date = request('date');
        if (!$userId) {
            throw new ParamInvalidException('用户ID不能为空');
        }
        if (!$date) {
            throw new ParamInvalidException('日期不能为空');
        }
        $data = InviteStatisticsService::getInviteStatisticsByDate($userId, $date);

        $totalCount = InviteStatisticsService::getInviteUserCountById($userId);
        $todayInviteCount = InviteStatisticsService::getTodayInviteCountById($userId);

        return Result::success([
            'data' => $data,
            'totalCount' => $totalCount,
            'todayInviteCount' => $todayInviteCount,
        ]);
    }
}