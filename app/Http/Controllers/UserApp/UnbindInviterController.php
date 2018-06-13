<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/12
 * Time: 12:14
 */

namespace App\Http\Controllers\UserApp;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Invite\InviteUserUnbindRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Result;

/**
 * 解除绑定关系控制器
 * Class UnbindInviterController
 * @package App\Http\Controllers
 */
class UnbindInviterController extends Controller
{

    /**
     * 获取已绑定的细信息
     */
    public function getBindInfo()
    {
        $userId = request()->get('current_user')->id;
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if(empty($inviteRecord)){
            throw new BaseResponseException('未绑定邀请人');
        }
        $mappingUser = null; // 上级商户或运营中心关联的用户
        $merchant = null; // 关联的上级商户
        $oper = null; // 关联的上级运营中心
        $user = null; // 关联的上级用户
        if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT){
            $merchant = Merchant::where('id', $inviteRecord->origin_id)->first();
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_MERCHANT)
                ->first();
            if(!empty($userMapping)){
                $mappingUser = User::find($userMapping->user_id);
            }
        }else if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_OPER){
            $oper = Oper::where('id', $inviteRecord->origin_id)->first();
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_OPER)
                ->first();
            if(!empty($userMapping)){
                $mappingUser = User::find($userMapping->user_id);
            }
        }else {
            $user = User::find($inviteRecord->origin_id);
        }

        return Result::success([
            'origin_type' => $inviteRecord->origin_type,
            'user' => $user,
            'merchant' => $merchant,
            'oper' => $oper,
            'mappingUser' => $mappingUser,
        ]);
    }

    /**
     * 解綁用戶信息
     */
    public function unbind()
    {
        $userId = request()->get('current_user')->id;
        //獲取解綁記錄
        $UnbindInviteRecordid  = InviteUserUnbindRecord:: where([
            ['user_id', '=', $userId],
            ['status', '=', '2'],
        ])->first();
        if(!empty($UnbindInviteRecordid)){
            throw new BaseResponseException('该用户已解绑一次，不能再次解绑');
        }else{
            $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
            if(empty($inviteRecord)){
                throw new BaseResponseException('未绑定邀请人');
            }else{
                $result =  InviteUserRecord::where('user_id',$userId)->delete();
                if($result){
                    $user = new InviteUserUnbindRecord();
                    $user->user_id = $userId;
                    $user->status = 2;
                        if($user->save()){
                            return Result::success();
                        }}
                }

            }

        }


}