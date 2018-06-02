<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/6/2
 * Time: 18:01
 */

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Oper\Oper;
use App\Result;

class OperSettingController extends Controller
{
    public function getPayToPlatformStatus()
    {
        $payToPlatform = Oper::where('id', request()->get('current_user')->oper_id)
            ->value('pay_to_platform');

        return Result::success([
            'pay_to_platform' => $payToPlatform,
        ]);
    }

    public function setPayToPlatformStatus()
    {
        $oper = Oper::findOrFail(request()->get('current_user')->oper_id);
        $oper->pay_to_platform = 1;
        $oper->save();

        return Result::success($oper);
    }
}