<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/13
 * Time: 18:48
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Tps\TpsBind;
use App\Modules\Tps\TpsBindService;
use App\Result;

class TpsBindController extends Controller
{

    public function getBindInfo()
    {
        $userId = request()->get('current_user')->id;
        $bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($userId, TpsBind::ORIGIN_TYPE_USER);
        return Result::success($bindInfo);
    }

    public function bindAccount()
    {
        $account = request('account');
        $password = request('password');

        $userId = request()->get('current_user')->id;
        TpsBindService::bindTpsAccountForUser($userId, $account, $password);
    }
}