<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/12
 * Time: 12:14
 */

namespace App\Http\Controllers\User;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserRecord;

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
        $inviteRecord = InviteUserRecord::where('user_id', request()->get('current_user')->id);
        if(empty($inviteRecord)){
            throw new BaseResponseException('未绑定邀请人');
        }

    }
}