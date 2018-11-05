<?php
/**
 * 结算流水管理
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\SettlementPlatformBatche\SettlementPlatformBatchesService;
use App\Result;

class SettlementPlatformBatchesController extends Controller
{

    /**
     * 获取结算流水列表
     *
     */
    public function getList()
    {
        $data = SettlementPlatformBatchesService::getList();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function modifyStatus()
    {
        $id = request()->get('id');
        $settlement = SettlementPlatformBatchesService::getByIdModifyStatus($id);
        return Result::success($settlement);
    }

}