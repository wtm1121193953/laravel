<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/1
 * Time: 15:43
 */

namespace App\Http\Controllers\UserApp;


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