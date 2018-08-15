<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 19:20
 */

namespace App\Modules\User;

use App\BaseService;
use App\Modules\Invite\InviteService;
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

            $parentName = InviteService::getParentName($item->id);
            if($parentName){
                $item->isBind = 1;
                $item->parent = $parentName;
            }else {
                $item->parent = '未绑定';
                $item->isBind = 0;
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