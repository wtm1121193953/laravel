<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\DataNotFoundException;
use App\Exports\OperInviteChannelExport;
use App\Exports\OperInviteRecordsExport;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Modules\Wechat\WechatService;
use App\Result;

class InviteChannelController extends Controller
{

    public function getList()
    {
        $keyword = request('keyword', '');
        $operId = request()->get('current_user')->oper_id;
        $page = request('page', 1);
        $pageSize = request('pageSize', 15);
        $orderColumn = request('orderColumn', null);
        $orderType = request('orderType', null);

        $param = [
            'page' => $page,
            'pageSize' => $pageSize,
            'orderColumn' => $orderColumn,
            'orderType' => $orderType,
        ];

        $data = InviteChannelService::getOperInviteChannelsByOperId($operId, $keyword, false, $param);

        return Result::success([
            'list' => $data['data'],
            'total' => $data['total']
        ]);
    }

    public function export()
    {
        $keyword = request('keyword', '');
        $operId = request()->get('current_user')->oper_id;
        $query = InviteChannelService::getOperInviteChannelsByOperId($operId, $keyword, true);
        return (new OperInviteChannelExport($query))->download('推广渠道列表.xlsx');
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required'
        ]);
        $name = request('name');
        $remark = request('remark', '');
        $operId = request()->get('current_user')->oper_id;

        $inviteChannel = InviteChannelService::createOperInviteChannel($operId, $name, $remark);

        return Result::success($inviteChannel);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $name = request('name');
        $remark = request('remark', '');
        $operId = request()->get('current_user')->oper_id;

        $inviteChannel = InviteChannelService::updateOperInviteChannel(request('id'), $operId, $name, $remark);

        return Result::success($inviteChannel);
    }

    /**
     * 下载邀请注册的小程序码
     * @deprecated todo 去掉下载二维码操作, 由统一下载控制器下载
     */
    public function downloadInviteQrcode()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        // qrcodeSizeType 小程序码尺寸类型, 1-小(8cm, 对应258px) 2-中(15cm, 对应430px)  3-大(50cm, 对应1280px)
        $qrcodeSizeType = request('qrcodeSizeType', 1);
        $operId = request()->get('current_user')->oper_id;

        $inviteChannel = InviteChannelService::getById($id);
        if(!$inviteChannel
            || $inviteChannel->origin_id != $operId
            ||  $inviteChannel->origin_type != InviteChannel::ORIGIN_TYPE_OPER) {
            throw new DataNotFoundException('邀请渠道信息不存在');
        }

        $scene = MiniprogramSceneService::getByInviteChannel($inviteChannel);

        $width = $qrcodeSizeType == 3 ? 1280 : ($qrcodeSizeType == 2 ? 430 : 258);

        $oper = OperService::getById($operId);
        if ($oper->pay_to_platform!=Oper::PAY_TO_OPER){

            $path = MiniprogramSceneService::genSceneQrCode($scene, $width,true,$inviteChannel->name);

            if(request()->ajax()){
                return Result::success(['name' => $path]);
            }else {
                return response()->download($path, '推广二维码-' . $inviteChannel->name . '-' . ['', '小', '中', '大'][$qrcodeSizeType] . '.png');
            }
        } else {
            $path = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);
            WechatService::addNameToAppCode($path, $inviteChannel->name);
            if(request()->ajax()){
                return Result::success(['name' => $path]);
            }else {
                return response()->download($path, '推广小程序码-' . $inviteChannel->name . '-' . ['', '小', '中', '大'][$qrcodeSizeType] . '.jpg');
            }
        }



    }

    /**
     * 获取邀请记录列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getInviteRecords()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');
        $mobile = request('mobile');
        $startTime = request('startTime');
        $endTime = request('endTime');
        $data = InviteUserService::getInviteRecordsByInviteChannelId(
            $id,
            compact('mobile', 'startTime', 'endTime')
        );
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function exportInviteRecords()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');
        $mobile = request('mobile');
        $startTime = request('startTime');
        $endTime = request('endTime');

        $query = InviteUserService::getInviteRecordsByInviteChannelId(
            $id,
            compact('mobile', 'startTime', 'endTime'),
            true
        );

        $inviteChannel = InviteChannelService::getById($id);

        return (new OperInviteRecordsExport($query))->download("推广渠道[{$inviteChannel->name}]注册用户记录.xlsx");
    }
}