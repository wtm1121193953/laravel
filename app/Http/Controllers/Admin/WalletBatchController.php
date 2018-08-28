<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Exports\WithdrawBatchExport;
use App\Http\Controllers\Controller;
use App\Modules\Wallet\WalletBatch;
use App\Modules\Wallet\WalletBatchService;
use App\Result;

class WalletBatchController extends Controller
{
    /**
     * 提现批次管理列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function walletBatchList()
    {
        $batchNo = request('batchNo', '');
        $type = request('type', '');
        $status = request('status', '');
        $start = request('startDate', '');
        $end = request('endDate', '');
        $pageSize = request('pageSize', 15);

        $start = $start ? date('Y-m-d 00:00:00', strtotime($start)) : '';
        $end = $end ? date('Y-m-d 23:59:59', strtotime($end)) : '';

        $params = compact('batchNo', 'type', 'status', 'start', 'end');
        $data = WalletBatchService::getWalletBatch($params, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 导出提现批次列表
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel()
    {
        $batchNo = request('batchNo', '');
        $type = request('type', '');
        $status = request('status', '');
        $start = request('startDate', '');
        $end = request('endDate', '');
        $pageSize = request('pageSize', 15);

        $start = $start ? date('Y-m-d 00:00:00', strtotime($start)) : '';
        $end = $end ? date('Y-m-d 23:59:59', strtotime($end)) : '';

        $params = compact('batchNo', 'type', 'status', 'start', 'end');
        $query = WalletBatchService::getWalletBatch($params, $pageSize, true);

        return (new WithdrawBatchExport($query))->download('提现批次列表.xlsx');
    }

    /**
     * admin 提现管理 添加批次
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add()
    {
        $this->validate(request(), [
            'type' => 'required|integer|min:1'
        ]);
        $type = request('type');
        $remark = request('remark', '');
        $batch = WalletBatchService::addBatch($type, $remark);
        return Result::success($batch);
    }

    /**
     * admin 提现管理 删除批次
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function delete()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        WalletBatchService::deleteBatch($id);

        return Result::success();
    }

    /**
     * admin 批次管理-批次明细 获取批次详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $batch = WalletBatchService::getById($id);
        return Result::success($batch);
    }

    /**
     * 批次明细 准备打款按钮功能 修改状态为准备打款
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changeBatchStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $batch = WalletBatchService::getById($id);
        if (empty($batch)) {
            throw new BaseResponseException('该批次不存在');
        }
        // 1.修改为准备打款
        $batch = WalletBatchService::changeStatus($batch, WalletBatch::STATUS_PREPARE_WITHDRAW);
        // 2.判断该批次是否都已经打款 且 该批次状态为准备打款，如果是则 修改状态为 打款完成
        if (WalletBatchService::checkBatchPayOrNot($batch->id) && $batch->status == WalletBatch::STATUS_PREPARE_WITHDRAW) {
            $batch = WalletBatchService::changeStatus($batch, WalletBatch::STATUS_WITHDRAW_SUCCESS);
        }
        return Result::success($batch);
    }
}