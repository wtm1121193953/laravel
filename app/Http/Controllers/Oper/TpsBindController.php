<?php

namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Tps\TpsBind;
use App\Result;
use App\Modules\Tps\TpsBindService;

class TpsBindController extends Controller
{

    public function getBindInfo()
    {

        //获取当前登录的帐号信息
        $originId = request()->get('current_user')->oper_id;
        //运营中心类型
        $bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($originId, TpsBind::ORIGIN_TYPE_OPER);

        return Result::success($bindInfo);

    }
}