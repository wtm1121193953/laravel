<?php

namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperBizerService;
use App\Result;

class BizerRecordController extends Controller {

    /**
     * 业务员申请
     * @return type
     */
    public function getList() {
        $where =[
            "oper_id" => request()->get('current_user')->oper_id,//登录所属运营中心ID
        ];
        //echo "<pre>";print_r($where);exit;
        $data = OperBizerService::getList($where);
        
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}
