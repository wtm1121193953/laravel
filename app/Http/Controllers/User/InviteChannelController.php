<?php

namespace App\Http\Controllers\User;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Result;

class InviteChannelController extends Controller
{

    /**
     * 获取邀请二维码码
     */
    public function getInviteQrcode()
    {
        $operId = request()->get('current_oper_id');
        $userId = request()->get('current_user')->id;
        $inviteChannel = InviteChannelService::getByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER, $operId);      // 获取邀请渠道
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

        /*if($inviteChannel->origin_type == InviteChannel::ORIGIN_TYPE_USER){
            throw new ParamInvalidException('会员二维码已经失效');
        }*/

        $inviteChannel->origin_name = InviteChannelService::getInviteChannelOriginName($inviteChannel);
        return Result::success($inviteChannel);
    }

    /**
     * 绑定推荐人
     * @throws \Exception
     */
    public function bindInviter()
    {
        $inviteChannelId = request('inviteChannelId');
        $inviteChannel = InviteChannelService::getById($inviteChannelId);
        if(empty($inviteChannel)){
            throw new ParamInvalidException('邀请渠道不存在');
        }
        InviteUserService::bindInviter(request()->get('current_user')->id, $inviteChannel);
        return Result::success();
    }

    /**
     * 用户分享列表统计接口
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getInviteUserStatistics()
    {
        $userId = request()->get('current_user')->id;
        $month = request('date');
        if (!$userId) {
            throw new ParamInvalidException('用户ID不能为空');
        }
        /*
         * data: {
         *      'Y-m': {count: xx, sub: []}
         * }
         */
        if($month){
            $result = InviteUserService::getInviteUsersByMonthAndUserId($userId, $month);
            $data = [
                $month => [
                    'count' => $result->total(),
                    'sub' => $result->items(),
                ]
            ];
        }else {
            $data = InviteUserService::getInviteUsersGroupByMonthForUser($userId);
        }
//        $data = InviteStatisticsService::getInviteStatListByDateForUser($userId, $month, $page);

        $totalCount = InviteStatisticsService::getTotalInviteCountByUserId($userId);
        $todayInviteCount = InviteStatisticsService::getTodayInviteCountByUserId($userId);

        return Result::success([
            'data' => $data,
            'totalCount' => $totalCount,
            'todayInviteCount' => $todayInviteCount,
        ]);
    }
}