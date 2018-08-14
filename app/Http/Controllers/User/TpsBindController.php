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

    /**
     * @throws \Exception
     */
    public function bindAccount()
    {
        $this->validate(request(), [
            'account' => 'required',
            'password' => 'required|min:6|max:18'
        ]);
        $account = request('account');
        $password = request('password');

        $userId = request()->get('current_user')->id;
        $bindInfo = TpsBindService::bindTpsAccountForUser($userId, $account, $password);
        return Result::success($bindInfo);
    }
}