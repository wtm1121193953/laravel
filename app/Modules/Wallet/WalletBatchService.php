<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use Illuminate\Support\Carbon;

class WalletBatchService extends BaseService
{

    /**
     * 获取提现批次列表
     * @param $params
     * @param int $pageSize
     * @param bool $withQuery
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder
     */
    public static function getWalletBatch($params, $pageSize = 15, $withQuery = false)
    {
        $batchNo = array_get($params, 'batchNo', '');
        $type = array_get($params, 'type', '');
        $status = array_get($params, 'status', '');
        $start = array_get($params, 'start', '');
        $end = array_get($params, 'end', '');

        $query = WalletBatch::query();

        if ($batchNo) {
            $query->where('batch_no', 'like', "%$batchNo%");
        }
        if ($type) {
            $query->where('type', $type);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if($start && $start instanceof Carbon){
            $start = $start->format('Y-m-d H:i:s');
        }
        if($end && $end instanceof Carbon){
            $end = $end->format('Y-m-d H:i:s');
        }
        if($start && $end){
            $query->whereBetween('created_at', [$start, $end]);
        }else if($start){
            $query->where('created_at', '>', $start);
        }else if($end){
            $query->where('created_at', '<', $end);
        }
        $query->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            return $data;
        }
    }

    /**
     * 根据id 获取提现批次
     * @param $id
     * @param array $field
     * @return WalletBatch
     */
    public static function getById($id, $field = ['*'])
    {
        $walletBatch = WalletBatch::find($id, $field);
        return $walletBatch;
    }

    /**
     * 添加批次
     * @param $type
     * @param string $remark
     * @return WalletBatch
     */
    public static function addBatch($type, $remark = '')
    {
        $batch = new WalletBatch();
        $batch->type = $type;
        $batch->remark = $remark;
        $batch->status = 1;
        $batch->save();

        $batch->batch_no = 'WB'. str_pad($batch->id, 8, "0", STR_PAD_LEFT);
        $batch->save();

        return $batch;
    }

    /**
     * 删除提现批次
     * @param $id
     * @return WalletBatch
     * @throws \Exception
     */
    public static function deleteBatch($id)
    {
        $batch = self::getById($id);
        if (empty($batch)) {
            throw new BaseResponseException('该批次不存在');
        }
        if ($batch->status != WalletBatch::STATUS_SETTLEMENT || $batch->total != 0) {
            throw new BaseResponseException('该批次不能删除');
        }
        $batch->delete();
        return $batch;
    }

    /**
     * 改变批次状态
     * @param WalletBatch $batch
     * @param $status
     * @return WalletBatch
     */
    public static function changeStatus(WalletBatch $batch, $status)
    {
        if ($status == WalletBatch::STATUS_PREPARE_WITHDRAW) {
            $batch->status = $status;
            $batch->save();
        } elseif ($status == WalletBatch::STATUS_WITHDRAW_SUCCESS) {
            if (self::checkBatchPayOrNot($batch->id)) {
                $batch->status = $status;
                $batch->save();
            } else {
                throw new BaseResponseException('该批次下存在未打款的提现订单');
            }
        } else {
            throw new ParamInvalidException('批次状态参数错误');
        }
        return $batch;
    }

    /**
     * 判断该批次下的 提现记录 是否都已经 打款（包含成功和失败）
     * @param $batchId
     * @return bool
     */
    public static function checkBatchPayOrNot($batchId)
    {
        $flag = true;
        WalletWithdraw::where('batch_id', $batchId)
            ->chunk(100, function ($withdraw) use (&$flag) {
                $withdraw->each(function ($item) use (&$flag) {
                    if (!in_array($item->status, [WalletWithdraw::STATUS_WITHDRAW_FAILED, WalletWithdraw::STATUS_WITHDRAW])) {
                        $flag = false;
                    }
                });
            });
        return $flag;
    }
}