<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exports\OperInviteChannelExport;
use App\Exports\OperInviteRecordsExport;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Modules\Wechat\WechatService;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class InviteChannelController extends Controller
{

    public function getList()
    {
        $keyword = request('keyword', '');
        $operId = request()->get('current_user')->oper_id;
        $data = InviteChannelService::getOperInviteChannels($operId, $keyword);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total()
        ]);
    }

    public function export()
    {
        $keyword = request('keyword', '');
        $operId = request()->get('current_user')->oper_id;
        $query = InviteChannelService::getOperInviteChannels($operId, $keyword, true);
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

        $path = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);

        if(request()->ajax()){
            return Result::success(['name' => $path]);
        }else {
            return response()->download($path, '推广小程序码-' . $inviteChannel->name . '-' . ['', '小', '中', '大'][$qrcodeSizeType] . '.jpg');
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
        $data = InviteUserRecord::where('invite_channel_id', $id)
            ->whereHas('user', function (Builder $query) use ($mobile, $startTime, $endTime) {
                $query->when($mobile, function (Builder $query) use ($mobile){
                    $query->where('mobile', 'like', "%$mobile%");
                })
                    ->when($startTime && $endTime, function (Builder $query) use ($startTime, $endTime){
                        $query->whereBetween('created_at', [$startTime, $endTime]);
                    })
                    ->when($startTime && !$endTime, function (Builder $query) use ($startTime){
                        $query->where('created_at', '>=', $startTime);
                    })
                    ->when($endTime && !$startTime, function (Builder $query) use ($endTime) {
                        $query->where('created_at', '<=', $endTime);
                    })
                ;
            })
            ->with('user:id,mobile,created_at')
            ->orderByDesc('user_id')
            ->paginate();
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
        $query = InviteUserRecord::where('invite_channel_id', $id)
            ->whereHas('user', function (Builder $query) use ($mobile, $startTime, $endTime) {
                $query->when($mobile, function (Builder $query) use ($mobile){
                    $query->where('mobile', 'like', "%$mobile%");
                })
                    ->when($startTime && $endTime, function (Builder $query) use ($startTime, $endTime){
                        $query->whereBetween('created_at', [$startTime, $endTime]);
                    })
                    ->when($startTime && !$endTime, function (Builder $query) use ($startTime){
                        $query->where('created_at', '>=', $startTime);
                    })
                    ->when($endTime && !$startTime, function (Builder $query) use ($endTime) {
                        $query->where('created_at', '<=', $endTime);
                    })
                ;
            })
            ->with('user:id,mobile,created_at');
        $inviteChannel = InviteChannelService::getById($id);

        return (new OperInviteRecordsExport($query))->download("推广渠道[{$inviteChannel->name}]注册用户记录.xlsx");
    }
}