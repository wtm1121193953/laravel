<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/7
 * Time: 21:44
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Modules\User\UserService;
use App\Result;
use App\Modules\Tps\TpsBindService;
use App\Modules\Tps\TpsBind;
use App\Modules\User\UserIdentityAuditRecordService;

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

        $user->level_text = User::getLevelText($user->level);
        $bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($user->id, TpsBind::ORIGIN_TYPE_USER);
        if ($bindInfo) {
            $user['tpsBindInfo'] = $bindInfo;
        } else {
            $user['tpsBindInfo'] = array();
        }

        $record = UserIdentityAuditRecordService::getRecordByUser($user->id);
        if ($record) {
            $user['identityInfoStatus'] = $record->status;
        } else {
            $user['identityInfoStatus'] = 4;
        }
        //查询我的上级
       $user['superior'] = InviteUserService::getParentName();

        return Result::success([
            'userInfo' => $user
        ]);
    }
}