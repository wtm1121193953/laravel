<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 19:20
 */

namespace App\Modules\User;

use App\BaseService;
use App\Modules\Merchant\MerchantService;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Oper\Oper;
use App\Modules\Invite\InviteUserRecord;

class UserService extends BaseService
{

    /**
     * 获取会员列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($params){

        $mobile = array_get($params, 'mobile');

        $users  = User::select('id','name','mobile','email','created_at')
            ->when($mobile, function (Builder $query) use ($mobile){
                $query->where('mobile','like','%'.$mobile.'%');
            })
            ->orderByDesc('created_at')
            ->paginate();

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

        return $users;
    }
    /**
     * 通过电话号码查询用户详情
     * @param $mobile
     * @return User
     */
    public static function getUserByMobile($mobile)
    {
        $user = User::where('mobile', $mobile)->first();
        return $user;
    }

    /**
     * 通过id获取用户信息
     * @param $userId
     * @return User
     */
    public static function getUserById($userId)
    {
        $user = User::find($userId);

        return $user;
    }
}