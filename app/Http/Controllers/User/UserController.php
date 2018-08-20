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
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Result;

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
        $userInfo->wx_nick_name = $wxUserInfo->nickName;
        $userInfo->wx_avatar_url = $wxUserInfo->avatarUrl;
        $userInfo->save();

        return Result::success();
    }
}