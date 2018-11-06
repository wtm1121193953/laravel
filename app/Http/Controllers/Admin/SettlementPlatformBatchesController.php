<?php
/**
 * 结算流水管理
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settlement\SettlementPlatformKuaiQianBatchService;
use App\Result;

class SettlementPlatformBatchesController extends Controller
{

    /**
     * 获取结算流水列表
     *
     */
    public function getList()
    {
        $data = SettlementPlatformKuaiQianBatchService::getList();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function modifyStatus()
    {
        $batchNo = request()->get('batch_no');
        SettlementPlatformKuaiQianBatchService::sendByBatchNo($batchNo);
        return Result::success('自动打款成功');
    }

}