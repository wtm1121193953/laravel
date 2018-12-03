<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/7
 * Time: 21:44
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Result;
use Illuminate\Filesystem\Cache;

class UserController extends Controller
{

    /**
     * 获取当前用户信息
     */
    public function getInfo()
    {
        $user = request()->get('current_user');

        $userMapping = UserMapping::where('user_id', $user->id)->first();
        if (!empty($userMapping)){
            if ($userMapping->origin_type == 1){
                $merchant = Merchant::findOrFail($userMapping->origin_id);
                $user->mapping_merchant_name = $merchant->name;
                $user->merchant_level = $merchant->level;
                $user->merchant_level_text = Merchant::getLevelText($merchant->level);
            }else{
                $oper = Oper::findOrFail($userMapping->origin_id);
                $user->mapping_oper_name = $oper->name;
            }
        }

        $user->level_text = User::getLevelText($user->level);
        $user->custom_service_email = config('common.custom_service_email');
        //查询我的上级
        $superior = InviteUserService::getParentName($user->id);
        if ($superior) {
            $user['superior'] = $superior;
        } else {
            $user['superior'] = '';
        }
        return Result::success([
            'userInfo' => $user
        ]);
    }

    /**
     * 用户授权更新用户的微信信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function updateUserWxInfo()
    {
        $user = request()->get('current_user');

        $wxUserInfo = json_decode(request('userInfo'));
        $userInfo = User::find($user->id);
        if (empty($userInfo)) {
            throw new BaseResponseException('该用户不存在');
        }
        if (!empty($wxUserInfo)){
            $userInfo->wx_nick_name = $wxUserInfo->nickName;
            $userInfo->wx_avatar_url = $wxUserInfo->avatarUrl;
        }
        $userInfo->save();

        return Result::success();
    }

    /**
     * 设置个人头像接口
     */
    public function setAvatar()
    {
        $user = request()->get('current_user');
        $avatarUrl = request('avatar_url');
        $name = request('name');
        $userInfo = User::find($user->id);
        if (empty($userInfo)) {
            throw new BaseResponseException('该用户不存在');
        }
        if ($avatarUrl){
            $userInfo->avatar_url = $avatarUrl;
        }

        if ($name){
            $userInfo->name = $name;
        }
        $userInfo->save();

        // 修改用户信息后更新缓存中的用户信息
        $token = request()->headers->get('token');
        Cache::put('token_to_user_' . $token, $userInfo, 60 * 24 * 30);

        return Result::success();
    }
}