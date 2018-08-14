<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\User\User;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
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
        $mobile = request('keyword');
        $users  = User::select('id','name','mobile','email','created_at')
            ->when($mobile, function (Builder $query) use ($mobile){
                $query->where('mobile','like','%'.$mobile.'%');
            })->orderByDesc('created_at')->paginate();
        $users->each(function ($item){
            $inviteRecord = InviteUserRecord::select('origin_id','origin_type')->where('user_id',$item->id)->first();
            if(empty($inviteRecord)){
                $item->parent = '未知-推荐信息';
                $item->isBind = 0;
            }else{
                $item->isBind = 1;
                //1-用户 2-商户 3-运营中心
                if($inviteRecord->origin_type == 1){
                    $user = User::where('id',$inviteRecord->origin_id)->first(['name','mobile']);
                    $item->parent = !empty($user) ? ($user->name ? $user->name:$user->mobile) : '未知-推荐人';
                }elseif ($inviteRecord->origin_type == 2){
                    $merchant = MerchantService::getById($inviteRecord->origin_id, ['name']);
                    $item->parent = !empty($merchant) ? $merchant->name : '未知-推荐商户';
                }elseif ($inviteRecord->origin_type == 3){
                    $oper = Oper::where('id',$inviteRecord->origin_id)->first(['name']);
                    $item->parent = !empty($oper) ? $oper->name : '未知-推荐运营中心';
                }else{
                    $item->parent = '未知-推荐信息';
                }
            }
        });
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
}
