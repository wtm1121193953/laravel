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
        header("content-type:text/html;charset=utf-8");
        $list = SettlementPlatformKuaiQianBatch::where('status',SettlementPlatformKuaiQianBatch::STATUS_NOT_SEND)->get();

        $kuaiqian = new KuaiQian();
        $list->each(function ($item) use ($kuaiqian) {

            $rs = $kuaiqian->send($item);

            dd($rs);
        });
        dd($list);
    }

    public static function batchQuery()
    {
        header("content-type:text/html;charset=utf-8");
        $list = SettlementPlatformKuaiQianBatch::where('status',SettlementPlatformKuaiQianBatch::STATUS_SENDED)->get();

        $kuaiqian = new KuaiQian();
        $list->each(function ($item) use ($kuaiqian) {

            $rs = $kuaiqian->queryByBatchNo($item);

            dd($rs);
        });
        dd($list);
    }
}
