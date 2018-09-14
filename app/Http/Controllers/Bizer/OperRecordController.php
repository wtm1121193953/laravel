<?php

namespace App\Http\Controllers\Bizer;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperBizerService;
use App\Result;

class OperRecordController extends Controller {

    /**
     * 运营中心申请列表
     */
    public function getList() {
        $where =[
            "bizer_id" => request()->get('current_user')->id,//登录所属业务员ID
        ];
        $data = OperBizerService::getList($where);
        
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}
