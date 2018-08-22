<?php

namespace App\Http\Controllers\Bizer;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperBizer;
use App\Result;

class OperController extends Controller {

    /**
     * 已添加的运营中心列表
     * @author tong.chen
     * @date 2018-8-22
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
    
    /**
     * 添加运营中心列表
     * @author tong.chen
     * @date 2018-8-22
     */
    public function getAddList() {

        $data = OperService::getAll(['status' => 1], 'id,name');

        return Result::success([
                    'list' => $data,
                    'total' => $data->total(),
        ]);
    }

    /**
     * 添加运营中心
     * @author tong.chen
     * @date 2018-8-22
     */
    public function add() {
        $this->validate(request(), [
            'operId' => 'required',
            'bizerId' => 'required',
            'remark' => 'required'
        ]);

        $operId = request('operId');
        $bizerId = request('bizerId');
        $remark = request('remark');
//        $model = new OperBizer();

        $model = OperBizer::where('oper_id', $operId)->where('bizer_id', $bizerId)->first();

        $model->oper_id = $operId;
        $model->bizer_id = $bizerId;
        $model->remark = $remark;
        $model->status = 0;

        $model->save();

        return Result::success();
    }

}
