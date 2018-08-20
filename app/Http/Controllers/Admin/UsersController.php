<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserService;
use App\Modules\User\User;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Oper\OperService;
use App\Modules\User\UserService;
use App\Result;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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
     * @throws \Exception
     */
    public function unBind(){

        $uid    = request('id');
        $record = InviteUserRecord::where('user_id', $uid)->first();

        if(!empty($record)){

            try{
                DB::beginTransaction();

                InviteUserService::unbindInviter($record);

                DB::commit();

            }catch (\Exception $e){
                DB::rollBack();
                throw $e;
            }

            $user = User::select('id','name','mobile','email','created_at')->first();
            return Result::success($user);
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

        $query = InviteChannelService::getOperInviteChannels([
            'operName' => $operName,
            'inviteChannelName' => $inviteChannelName
        ], true);
        $data = $query->paginate($pageSize);
        $data->each(function ($item) {
            $item->operName = OperService::getNameById($item->oper_id);
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
            $query = InviteUserService::getInviteRecordsByInviteChannelId($id, compact('mobile'), true);
            $total = $query->count();
            $data = $query->get();

            return Result::success([
                'list' => $data,
                'total' => $total,
            ]);
        }else {
            $data = InviteUserService::getInviteRecordsByInviteChannelId($id, compact('mobile'));

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
        // 接受参数
        $isAll = request('isAll', false);
        $mobile = request('mobile');
        $inviteUserRecordIds = request('inviteUserRecordIds', []);
        $inviteChannelId = request('inviteChannelId', 0);
        $currentUser = request()->get('current_user');

        // 获取要换绑的目标用户
        $user = UserService::getUserByMobile($mobile);
        if (empty($user)) {
            throw new BaseResponseException('换绑的用户不存在');
        }

        // 获取原邀请渠道
        $oldInviteChannel = InviteChannelService::getById($inviteChannelId);
        if (empty($oldInviteChannel)) {
            throw new BaseResponseException('原邀请渠道不存在');
        }

        // 获取需要换绑的邀请记录
        if ($isAll) {
            $query = InviteUserService::getInviteRecordsByInviteChannelId($inviteChannelId, [], true);
            $inviteUserRecords = $query->get();  //需换绑的记录
        } else {
            $inviteUserRecords = InviteUserService::getInviteRecordsByIds($inviteUserRecordIds); //需换绑的记录
        }

        try {
            DB::beginTransaction();
            // 查找换绑后的邀请渠道，没有则创建新的邀请渠道, 创建邀请渠道时, 使用固定的运营中心ID
            $fixedOperId = App::environment('production') ? 1 : 3;
            $newInviteChannel = InviteChannelService::getByOriginInfo($user->id, InviteChannel::ORIGIN_TYPE_USER, $fixedOperId);

            //首先操作invite_user_change_bind_records表，写入换绑记录
            $inviteUserBatchChangedRecord = InviteUserService::batchChangeInviter($oldInviteChannel, $newInviteChannel, $currentUser, $inviteUserRecords);

            DB::commit();
        } catch(BaseResponseException $e){
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage() ?: '换绑失败';
            throw new BaseResponseException($message);
        }

        return Result::success([
            'successCount' => $inviteUserBatchChangedRecord->change_bind_number,
            'errorCount' => $inviteUserBatchChangedRecord->change_error_number,
        ]);
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

        $data = InviteUserService::getBatchChangedRecords(compact('operName', 'inviteChannelName'), $pageSize);

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
        $batchRecordId = request('id');
        $pageSize = request('pageSize', 15);

        $data = InviteUserService::getUnbindRecordsByBatchId($batchRecordId, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}
