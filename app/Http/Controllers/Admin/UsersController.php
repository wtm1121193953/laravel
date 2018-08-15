<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Jobs\InviteUserStatisticsDailyJob;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteService;
use App\Modules\Invite\InviteUserChangeBindRecordService;
use App\Modules\Invite\InviteUserUnbindRecordService;
use App\Modules\User\User;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Oper\OperService;
use App\Modules\User\UserService;
use App\Result;
use Illuminate\Support\Facades\DB;
use App\Modules\Invite\InviteUserUnbindRecord;
use App\ResultCode;

class UsersController extends Controller
{
    /**
     * 获取会员列表
     */
    public function getList()
    {
        $keyword = request('keyword');
        $users = UserService::getList([
            'mobile' => $keyword
        ]);
        return Result::success([
            'list' => $users->items(),
            'total' => $users->total(),
        ]);
    }


    /**
     * 后台解绑用户推荐关系
     */
    public function unBind(){

        $uid    = request('id');
        $record = InviteUserRecord::where('user_id',$uid)->first(['user_id','origin_id']);

        if(!empty($record)){

            try{
                DB::beginTransaction();

                $inviteUserUnbindRecord = new InviteUserUnbindRecord();
                $inviteUserUnbindRecord->user_id = $record->user_id;
                $inviteUserUnbindRecord->status  = 2;
                $inviteUserUnbindRecord->save();

                InviteUserRecord::where('user_id',$record->user_id)->limit(1)->delete();

                DB::commit();

                $user = User::select('id','name','mobile','email','created_at')->first();
                return Result::success($user);

            }catch (\Exception $e){
                DB::rollBack();
                throw new BaseResponseException("未知错误", ResultCode::UNKNOWN);
            }
        }else{
            throw new BaseResponseException("已解绑", ResultCode::UNKNOWN);
        }
    }

    /**
     * 获取渠道换绑列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getChangeBindList()
    {
        $operName = request('operName', '');
        $inviteChannelName = request('inviteChannelName', '');
        $pageSize = request('pageSize', 15);

        $query = InviteChannelService::getAllOperInviteChannels(true, $operName, $inviteChannelName);
        $data = $query->paginate($pageSize);
        $data->each(function ($item) {
            $oper = OperService::detail($item->oper_id);
            $item->operName = $oper->name;
        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取换绑渠道邀请注册人数的列表详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getInviteUsersList()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');
        $mobile = request('mobile', '');
        $noPaginate = request('noPaginate', false);
        if ($noPaginate) {
            $query = InviteService::getRecordsByInviteChannelId($id, compact('mobile'), true);
            $total = $query->count();
            $data = $query->get();

            return Result::success([
                'list' => $data,
                'total' => $total,
            ]);
        }else {
            $data = InviteService::getRecordsByInviteChannelId($id, compact('mobile'));

            return Result::success([
                'list' => $data->items(),
                'total' => $data->total(),
            ]);
        }
    }

    /**
     * 执行换绑操作
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changeBind()
    {
        $this->validate(request(), [
            'isAll' => 'required',
            'mobile' => 'required',
        ]);
        $isAll = request('isAll', false);
        $mobile = request('mobile');
        $inviteUserRecordIds = request('inviteUserRecordIds', []);
        $inviteChannelId = request('inviteChannelId', 0);
        $currentUser = request()->get('current_user');

        $user = UserService::getUserByMobile($mobile);
        if (empty($user)) {
            throw new BaseResponseException('换绑的用户不存在');
        }

        $oldInviteChannel = InviteChannelService::getById($inviteChannelId);
        if (empty($oldInviteChannel)) {
            throw new BaseResponseException('原邀请渠道不存在');
        }

        if ($isAll) {
            $query = InviteService::getRecordsByInviteChannelId($inviteChannelId, [], true);
            $changeBindNumber = $query->count(); //换绑数量
            $inviteUserRecords = $query->get();  //需换绑的记录
        } else {
            $changeBindNumber = count($inviteUserRecordIds); //换绑数量
            $inviteUserRecords = InviteService::getRecordsByIds($inviteUserRecordIds); //需换绑的记录
        }

        try {
            DB::beginTransaction();
            // 查找换绑后的邀请渠道，没有则创建新的邀请渠道
            $newInviteChannel = InviteChannelService::getByOriginInfo($user->id, InviteChannel::ORIGIN_TYPE_USER, InviteChannel::FIXED_OPER_ID);

            //首先操作invite_user_change_bind_records表，写入换绑记录
            $inviteUserChangeBindRecord = InviteUserChangeBindRecordService::createChangeBindRecord($oldInviteChannel, $mobile, $changeBindNumber, $currentUser);

            $needStatisticsDate = []; //需要统计的日期
            // 循环遍历需换绑的记录，在解绑表invite_user_unbind_records中加入解绑记录;
            // 添加新的邀请记录在invite_user_records中,并删除记录表中的旧记录
            foreach ($inviteUserRecords as $inviteUserRecord) {
                $needStatisticsDate[] = $inviteUserRecord->created_at;

                InviteUserUnbindRecordService::createUnbindRecord($inviteUserRecord->user_id, InviteUserUnbindRecord::STATUS_UNBIND, $inviteUserChangeBindRecord->id, $inviteUserRecord);
                InviteService::bindInviter($inviteUserRecord->user_id, $newInviteChannel, $inviteUserRecord);
                $inviteUserRecord->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            throw new BaseResponseException('换绑失败');
        }

        // 记录换绑完成之后，对每日统计表invite_user_statistics_dailies进行更改
        if (!empty($needStatisticsDate)) {
            foreach ($needStatisticsDate as $date) {
                InviteUserStatisticsDailyJob::dispatch($date);
            }
        }

        return Result::success();
    }

    /**
     * 获取换绑记录列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getChangeBindRecordList()
    {
        $operName = request('operName', '');
        $inviteChannelName = request('inviteChannelName', '');
        $pageSize = request('pageSize');

        $data = InviteUserChangeBindRecordService::getChangeBindRecordList(compact('operName', 'inviteChannelName'), $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取换绑人列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getChangeBindPeopleRecordList()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $changeBindRecordId = request('id');
        $pageSize = request('pageSize', 15);

        $data = InviteUserUnbindRecordService::getUnbindRecordList(compact('changeBindRecordId'), $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}
