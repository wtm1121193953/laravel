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
use App\Result;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InviteChannelController extends Controller
{

    public function getInviteQrcode()
    {
        $userId = request()->get('current_user')->id;
        $inviteChannel = InviteChannelService::getByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER);
        $dir = storage_path('app/public/inviteChannel/qrcode');
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        $filename = "{$inviteChannel->id}_375.png";
        $path = $dir . "/{$filename}";
        if(!is_file($path)){
            QrCode::format('png')->errorCorrection('H')->encoding('UTF-8')->margin(3)->size(375)->generate(json_encode([
                'type' => 'inviteChannel',
                'value' => ['id' => $inviteChannel->id],
            ]), $path);
        }

        return Result::success([
            'qrcode_url' => asset('storage/inviteChannel/qrcode/' . $filename),
        ]);
    }

    /**
     * 根据场景ID获取邀请人信息
     */
    public function getInviterByChannelId()
    {
        $inviteChannelId = request('inviteChannelId');
        if(empty($inviteChannelId)){
            throw new ParamInvalidException('邀请渠道ID不能为空');
        }
        $inviteChannel = InviteChannelService::getById($inviteChannelId);
        if(empty($inviteChannel)){
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
        if(empty($inviteChannel)){
            throw new ParamInvalidException('邀请渠道不存在');
        }
        InviteUserService::bindInviter(request()->get('current_user')->id, $inviteChannel);
        return Result::success();
    }

}