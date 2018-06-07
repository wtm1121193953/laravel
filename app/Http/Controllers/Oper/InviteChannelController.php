<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\NoPermissionException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteService;
use App\Modules\Wechat\MiniprogramScene;
use App\Modules\Wechat\WechatService;
use App\Result;

class InviteChannelController extends Controller
{

    public function getList()
    {
        $operId = request()->get('current_user')->oper_id;
        $data = InviteChannel::where('origin_id', $operId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_OPER)
            ->paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total()
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required'
        ]);
        $name = request('name');
        $remark = request('remark');
        $operId = request()->get('current_user')->oper_id;

        $inviteChannel = new InviteChannel();
        $inviteChannel->oper_id = $operId;
        $inviteChannel->origin_id = $operId;
        $inviteChannel->origin_type = InviteChannel::ORIGIN_TYPE_OPER;
        $inviteChannel->name = $name;
        $inviteChannel->remark = $remark;

        $scene = new MiniprogramScene();
        $scene->oper_id = $operId;
        $scene->page = MiniprogramScene::PAGE_INVITE_REGISTER;
        $scene->type = MiniprogramScene::TYPE_INVITE_CHANNEL;
        $scene->payload = json_encode([
            'origin_id' => $operId,
            'origin_type' => InviteChannel::ORIGIN_TYPE_OPER,
        ]);
        $scene->save();

        $inviteChannel->scene_id = $scene->id;
        $inviteChannel->save();
        return Result::success($inviteChannel);
    }

    public function edit()
    {

        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $name = request('name');
        $remark = request('remark');
        $operId = request()->get('current_user')->oper_id;

        $inviteChannel = InviteChannel::find(request('id'));
        if(empty($inviteChannel)){
            throw new ParamInvalidException('邀请渠道不存在');
        }

        if($inviteChannel->origin_id != $operId){
            throw new NoPermissionException('无权限修改');
        }
        $inviteChannel->name = $name;
        $inviteChannel->remark = $remark;
        $inviteChannel->save();
        return Result::success($inviteChannel);
    }

    /**
     * 下载邀请注册的小程序码
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function downloadInviteQrcode()
    {
        // type 小程序码类型, 1-小(8cm, 对应258px) 2-中(15cm, 对应430px)  3-大(50cm, 对应1280px)
        $type = request('type', 1);
        $operId = request()->get('current_user')->oper_id;
        $inviteChannel = InviteChannel::where('oper_id', $operId)
            ->where('origin_id', $operId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_OPER)
            ->firstOrFail();
        $scene = MiniprogramScene::findOrFail($inviteChannel->scene_id);
        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);
        $inviteQrcodeFilename = WechatService::genMiniprogramAppCode($operId, $scene->id, $scene->page, $width, true);
        $filename = storage_path('app/public/miniprogram/app_code') . '/' . $inviteQrcodeFilename;
        return response()->download($filename, '分享会员二维码_' . ['', '小', '中', '大'][$type] . '.jpg');

    }
}