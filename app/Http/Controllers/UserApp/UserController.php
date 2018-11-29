<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/7
 * Time: 21:44
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\User\UserIdentityAuditRecord;
use App\Modules\User\UserMapping;
use App\Modules\User\UserService;
use App\Result;
use App\Modules\Tps\TpsBindService;
use App\Modules\Tps\TpsBind;
use App\Modules\User\UserIdentityAuditRecordService;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{

    /**
     * 获取当前用户信息
     */
    public function getInfo()
    {
        $user = request()->get('current_user');

        $userMapping = UserMapping::where('user_id', $user->id)->first();
        if (!empty($userMapping)) {
            if ($userMapping->origin_type == 1) {
                $merchant = Merchant::findOrFail($userMapping->origin_id);
                $user->mapping_merchant_name = $merchant->name;
                $user->merchant_level = $merchant->level;
                $user->merchant_level_text = Merchant::getLevelText($merchant->level);
            } else {
                $oper = Oper::findOrFail($userMapping->origin_id);
                $user->mapping_oper_name = $oper->name;
            }
        }

        $dbUser = UserService::getUserById($user->id);

        $user->name = $dbUser->name;

        $user->avatar_url = UserService::getUserAvatarUrlByUserId($user->id);

        $user->level_text = User::getLevelText($user->level);
        $user->custom_service_email = config('common.custom_service_email');
        $bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($user->id, TpsBind::ORIGIN_TYPE_USER);
        if ($bindInfo) {
            $user['tpsBindInfo'] = $bindInfo;
        } else {
            $user['tpsBindInfo'] = null;
        }


        $record = UserIdentityAuditRecordService::getRecordByUserId($user->id);
        $user['identityInfoStatus'] = ($record) ? $record->status : UserIdentityAuditRecord::STATUS_UN_SAVE;
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