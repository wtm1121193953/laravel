<?php

namespace App\Modules\SettlementPlatformBatche;

use App\BaseService;

class SettlementPlatformBatchesService extends BaseService
{
    //
    public static function getList()
    {
        $data = settlementPlatformKuaiQianBatches::query()->orderByDesc('id')->paginate();
        return $data;
    }

    /**
     * 通过id获取结算单更新状态
     * @param $id
     * @return bool
     */
    public static function getByIdModifyStatus($id)
    {
        $data = settlementPlatformKuaiQianBatches::where('id', $id)->update(
            [
                'status' => settlementPlatformKuaiQianBatches::STATUS_CONFIRM,
            ]
        );
        return $data;
    }
}
