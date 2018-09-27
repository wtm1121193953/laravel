<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Result;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Modules\Invite\InviteStatisticsService;

class InviteChannelController extends Controller
{

    public function getInviteQrcode()
    {
        $userId = request()->get('current_user')->id;
        $inviteChannel = InviteChannelService::getByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER);
        $inviteChannel->origin_name = InviteChannelService::getInviteChannelOriginName($inviteChannel);
        $scene = MiniprogramSceneService::getByInviteChannel($inviteChannel);
        $url = MiniprogramSceneService::genSceneQrCode($scene);

        return Result::success([
            'qrcode_url' => $url,
        ]);
    }

    /**
     * 根据场景ID获取邀请人信息
     */
    public function getInviterByChannelId()
    {
        $inviteChannelId = request('inviteChannelId');
        if (empty($inviteChannelId)) {
            throw new ParamInvalidException('邀请渠道ID不能为空');
        }
        $inviteChannel = InviteChannelService::getById($inviteChannelId);
        if (empty($inviteChannel)) {
            throw new ParamInvalidException('渠道不存在');
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
        if (empty($inviteChannel)) {
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
        if ($month) {
            $result = InviteUserService::getInviteUsersByMonthAndUserId($userId, $month);
            $data = [
                'month' => $month,
                'count' => $result->total(),
                'sub' => $result->items(),
            ];
            $monthDetail = array($data);
        } else {
            $monthDetail = InviteUserService::getInviteUsersGroupByMonthForUser($userId);
            $result = array();
            foreach ($monthDetail as $key => $value){
                $value['month'] = $key;
                $result[] = $value;
            }
            $monthDetail = $result;
        }
//        $data = InviteStatisticsService::getInviteStatListByDateForUser($userId, $month, $page);

        $totalCount = InviteStatisticsService::getTotalInviteCountByUserId($userId);
        $todayInviteCount = InviteStatisticsService::getTodayInviteCountByUserId($userId);

        return Result::success([
            'monthDetail' => $monthDetail,
            'totalCount' => $totalCount,
            'todayInviteCount' => $todayInviteCount,
        ]);
    }

}