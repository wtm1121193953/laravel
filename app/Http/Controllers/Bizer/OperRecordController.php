<?php

namespace App\Http\Controllers\Bizer;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperBizerService;
use App\Result;

class OperRecordController extends Controller {

    /**
     * 运营中心申请列表
     */
    public function getList() {

        $startTime = request('startTime');
        $endTime = request('endTime');
        $name = request('name');
        $contacter = request('contacter');
        $tel = request('tel');
        $provinceId = request('provinceId');
        $cityId = request('cityId');
        $status = request('status');
        
//        $data = OperService::getAll(['status' => 1], 'id,name');
//
//        return Result::success([
//                    'list' => $data,
//                    'total' => $data->total(),
//        ]);
    }
}
