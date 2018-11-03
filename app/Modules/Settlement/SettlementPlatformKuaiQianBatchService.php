<?php

namespace App\Modules\Settlement;

use App\BaseService;

class SettlementPlatformKuaiQianBatchService extends BaseService
{
    public static function genBatchNo()
    {
        return 'dq'.date('YmdHis') . rand(10000,99999);
    }

    public static function batchSend()
    {
        $list = SettlementPlatformKuaiQianBatch::where('status',SettlementPlatformKuaiQianBatch::STATUS_NOT_SEND)->get();

        $list->each(function ($item) {

        });
        dd($list);
    }
}
