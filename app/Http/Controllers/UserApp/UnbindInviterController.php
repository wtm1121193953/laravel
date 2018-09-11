<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/12
 * Time: 12:14
 */

namespace App\Http\Controllers\UserApp;

use App\Http\Controllers\Controller;
use App\Modules\User\UserService;

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
        UserService::bindInfoForUserApp($userId);

    }

    /**
     * 解綁用戶信息
     * @throws \Exception
     */
    public function unbind()
    {
        $userId = request()->get('current_user')->id;
        UserService::unbindForUserApp($userId);

    }


}