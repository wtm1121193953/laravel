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

        if ($list->count() == 0) {
            return 'no batches';
        }

        $kuaiqian = new KuaiQian();
        $list->each(function ($item) use ($kuaiqian) {
            $rs = $kuaiqian->send($item);
        });

        return 'ok';
    }


    /**
     * 单个批次推送
     * @param $batch_no
     */
    public static function sendByBatchNo($batch_no)
    {
        //$batch_no = '';
        $batch = SettlementPlatformKuaiQianBatch::where('batch_no', $batch_no)->firstOrFail();
        $kuaiqian = new KuaiQian();
        $kuaiqian->send($batch);
    }

    public static function batchQuery()
    {
        header("content-type:text/html;charset=utf-8");
        $list = SettlementPlatformKuaiQianBatch::where('status',SettlementPlatformKuaiQianBatch::STATUS_SENDED)->get();

        if ($list->count() == 0) {
            return 'no batches';
        }

        $kuaiqian = new KuaiQian();
        $list->each(function ($item) use ($kuaiqian) {
            $rs = $kuaiqian->queryByBatchNo($item);
        });

        return 'ok';
    }


    public static function getList()
    {
        $data = SettlementPlatformKuaiQianBatch::query()->orderByDesc('id')->paginate();
        return $data;
    }

    /**
     * 通过id获取结算单更新状态
     * @param $id
     * @return bool
     */
    public static function getByIdModifyStatus($id)
    {
        $data = SettlementPlatformKuaiQianBatch::where('id', $id)->update(
            [
                'status' => SettlementPlatformKuaiQianBatch::STATUS_SENDED,
            ]
        );
        return $data;
    }
}
