<?php

namespace App\Http\Controllers\Bizer;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperBizer;
use App\Modules\Oper\OperBizerService;
use App\Exceptions\BaseResponseException;
use App\Result;
use App\Modules\Area\Area;
use Illuminate\Support\Facades\DB;

class OperController extends Controller {

    /**
     * 运营中心列表
     * @author tong.chen
     * @date 2018-8-22
     */
    public function getList() {

        $startTime = request('startTime');
        $endTime = request('endTime');
        $name = request('name');
        $contacter = request('contacter');
        $tel = request('tel');
//        $provinceId = request('provinceId');
        $areaIds = request('cityId');
        $status = request('status');
        $bizerId = request()->get('current_user')->id;

        $provinceId = 0;
        $cityId = 0;
        if(isset($areaIds[0])){
            $provinceId = Area::findOrFail($areaIds[0])->area_id;
        }
        if(isset($areaIds[1])){
            $cityId = Area::findOrFail($areaIds[1])->area_id;
        }
        $opers = OperService::getAll([
                    'name' => $name,
                    'contacter' => $contacter,
                    'tel' => $tel,
                    'province_id' => $provinceId,
                    'city_id' => $cityId
                        ], 'id');

        $operIds = [];
        $opers->each(function ($oper) use (&$operIds) {
            $operIds[] = $oper->id;
        });
        
        $data = OperBizerService::getList([
                    'bizer_id' => $bizerId,
                    'oper_ids' => $operIds ? $operIds : [0],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status
        ]);

        $tips = [];

        $data->each(function ($item) use (&$tips) {
            if($item->status == -1 && $item->is_tips == 0 && $item->note){
                $tips[] = [
                    'operName' => $item->operInfo->name,
                    'note' => $item->note,
                ];
            }
            OperBizerService::updateIsTipsById($item->id);
        });
        
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'tips' => $tips
        ]);
    }

    /**
     * 运营中心名称列表
     * @author tong.chen
     * @date 2018-8-22
     */
    public function getNameList() {
        $operName = request('operName', '');
        $params = [
            'status' => 1,
            'name' => $operName,
        ];
        $data = OperService::getAll($params, 'id,name');

        return Result::success([
            'list' => $data,
        ]);
    }

    /**
     * 添加运营中心
     * @author tong.chen
     * @date 2018-8-22
     * @throws \Exception
     */
    public function add() {
        $this->validate(request(), [
            'oper_id' => 'required',
        ]);

        $operId = request('oper_id');
        $bizerId = request()->get('current_user')->id;
        $remark = request('remark', '');

        DB::beginTransaction();
        try {
            OperBizerService::addOperBizer($operId, $bizerId, $remark);

            OperBizerService::addOperBizerLog($operId, $bizerId);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getResponse()->original['message'] ?: '添加运营中心失败';
            throw new BaseResponseException($msg);
        }

        return Result::success();
    }

}
