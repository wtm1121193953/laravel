<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exports\UserExport;
use App\Exports\UserIdentityExport;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateMarketingStatisticsInviteInfo;
use App\Modules\Country\CountryService;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Invite\InviteUserService;
use App\Modules\User\User;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Oper\OperService;
use App\Modules\User\UserIdentityAuditRecord;
use App\Modules\User\UserService;
use App\Result;
use Illuminate\Support\Carbon;
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
     * 获取会员列表
     */
    public function userList()
    {
        $mobile = request('mobile');
        $name = request('name');
        $id = request('id');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $identityStatus = request('identityStatus');
        $users = UserService::userList([
            'mobile' => $mobile,
            'id' => $id,
            'name' => $name,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'identityStatus' => $identityStatus
        ]);

        return Result::success([
            'list' => $users->items(),
            'total' => $users->total(),
        ]);
    }

    /**
     * 下载Excel
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        $mobile = request('mobile');
        $name = request('name');
        $id = request('id');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $identityStatus = request('identityStatus');

        $query = UserService::userList([
            'mobile' => $mobile,
            'id' => $id,
            'name' => $name,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'identityStatus' => $identityStatus
        ],true);

        return (new UserExport($query))->download('用户列表.xlsx');
    }


    /**
     * 获取会员审核列表
     */
    public function identity()
    {
        $mobile = request('mobile');
        $name = request('name');
        $id = request('id');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $idCardNo = request('id_card_no');
        $users = UserService::identity([
            'mobile' => $mobile,
            'id' => $id,
            'name' => $name,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'id_card_no'=>$idCardNo
        ]);

        return Result::success([
            'list' => $users->items(),
            'total' => $users->total(),
        ]);
    }

    public function batchIdentity()
    {
        $ids = request('ids');
        $type = request('type');
        $resson = request('reason');
        if ($type == 1) {
            if ($ids) {
                foreach ($ids as $id) {
                    $rs = UserService::identityDo($id, UserIdentityAuditRecord::STATUS_SUCCESS);
                }
            }
            return Result::success('操作成功');
        } elseif ($type==2) {
            if ($ids) {
                foreach ($ids as $id) {
                    $rs = UserService::identityDo($id, UserIdentityAuditRecord::STATUS_FAIL,$resson);
                }
            }
            return Result::success('操作成功');
        }


    }

    /**
     * 下载Excel
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function identityDownload()
    {
        $mobile = request('mobile');
        $name = request('name');
        $id = request('id');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');

        $query = UserService::identity([
            'mobile' => $mobile,
            'id' => $id,
            'name' => $name,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'id_card_no'=>''
        ],true);

        return (new UserIdentityExport($query))->download('用户审核列表.xlsx');
    }

    public function identityDetail()
    {
        $id = request('id');

        $info = UserService::identityDetail($id);
        $info->countryName = CountryService::getNameZhById($info->country_id);

        if (empty($info->user)) {
            throw new BaseResponseException("用户数据异常", ResultCode::UNKNOWN);
        }
        return Result::success($info);
    }

    public function identityDo()
    {
        $id = request('id');
        $status = request('status');
        $reason = request('reason');

        $rs = UserService::identityDo($id, $status, $reason);
        return Result::success('操作成功',['rs'=>$rs]);

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

                InviteStatisticsService::updateDailyStatByOriginInfoAndDate($record->origin_id, $record->origin_type, Carbon::createFromFormat('Y-m-d', $record->created_at->format('Y-m-d')));

                UpdateMarketingStatisticsInviteInfo::dispatch($record->origin_id, $record->origin_type, $record->created_at);

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
        $originType = request('originType', '');
        $mobile = request('mobile', '');
        $pageSize = request('pageSize', 15);

        $originIds = [];
        if ($mobile) {
            $originIds = UserService::getUserColumnArrayByMobile($mobile, 'id');
            if ($originIds->isEmpty()) {
                return Result::success([
                    'list' => [],
                    'total' => 0,
                ]);
            }
            $originType = InviteChannel::ORIGIN_TYPE_USER;
        }

        $query = InviteChannelService::getInviteChannels([
            'operName' => $operName,
            'inviteChannelName' => $inviteChannelName,
            'originType' => $originType,
            'originIds' => $originIds,
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
        set_time_limit(0);
        $this->validate(request(), [
            'isAll' => 'required',
            'channelIdOrMobile' => 'required',
            'type' => 'required',
        ]);
        // 接受参数
        $isAll = request('isAll', false);
        $type = request('type');
        $channelIdOrMobile = request('channelIdOrMobile');
        $inviteUserRecordIds = request('inviteUserRecordIds', []);
        $inviteChannelId = request('inviteChannelId', 0);
        $currentUser = request()->get('current_user');

        DB::beginTransaction();
        try {
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
            if ($inviteUserRecords->isEmpty()) {
                throw new BaseResponseException('没有需要换绑的记录');
            }

            // 获取要换绑的目标用户
            if ($type == InviteChannel::ORIGIN_TYPE_USER) {
                $user = UserService::getUserByMobile($channelIdOrMobile);
                if (empty($user)) {
                    throw new BaseResponseException('换绑的用户不存在');
                }

                // 查找换绑后的邀请渠道，没有则创建新的邀请渠道, 创建邀请渠道时, 使用固定的运营中心ID
                $fixedOperId = App::environment('production') ? 1 : 3;
                $newInviteChannel = InviteChannelService::getByOriginInfo($user->id, InviteChannel::ORIGIN_TYPE_USER, $fixedOperId);
            } else {
                $newInviteChannel = InviteChannelService::getById($channelIdOrMobile);
                if (empty($newInviteChannel)) {
                    throw new BaseResponseException('换绑的新渠道不存在');
                }
            }

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
