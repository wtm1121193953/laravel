<?php

namespace App\Modules\Settlement;

use App\BaseService;
use App\Support\AgentPay\KuaiQian;

class SettlementPlatformKuaiQianBatchService extends BaseService
{
    public static function genBatchNo()
    {
        return 'dq'.date('YmdHis') . rand(10000,99999);
    }

    public static function batchSend()
    {
        $list = SettlementPlatformKuaiQianBatch::where('status',SettlementPlatformKuaiQianBatch::STATUS_NOT_SEND)->get();

        $kuaiqian = new KuaiQian();
        $list->each(function ($item) use ($kuaiqian) {

            $rs = $kuaiqian->send($item->data_send);

            dd($rs);
        });
        dd($list);
    }
}
