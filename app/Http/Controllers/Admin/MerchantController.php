<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 15:34
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Exports\MerchantExport;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAudit;
use App\Modules\Merchant\MerchantAuditService;
use App\Modules\Merchant\MerchantCategory;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizMember;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{

    /**
     * 获取商户列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {

        $id = request('merchantId');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $name = request('name');
        $signboardName = request('signboardName');
        $auditStatus = request('auditStatus');
        if(is_string($auditStatus)){
            $auditStatus = explode(',', $auditStatus);
        }

        $operId = request('operId');
        if($operId  && !is_numeric( $operId )){
            throw new BaseResponseException('运营中心ID必须为数字');
        }
        // 根据输入的运营中心名称获取所属运营中心ID列表
        $operName = request('operName');
        if($operName) {
            $operIds = Oper::where('name', 'like', "%$operName%")
                ->select('id')->get()
                ->pluck('id');
        }
        $creatorOperId = request('creatorOperId');
        // 根据输入的运营中心名称获取录入信息的运营中心ID列表
        $creatorOperName = request('creatorOperName');
        if($creatorOperName){
            $createOperIds = Oper::where('name', 'like', "%$creatorOperName%")
                ->select('id')->get()
                ->pluck('id');
        }

        $data = MerchantService::getList([
            'id' => $id,
            'name' => $name,
            'signboardName' => $signboardName,
            'operId' => $operIds ?? $operId,
            'creatorOperId' => $createOperIds ?? $creatorOperId,
            'auditStatus' => $auditStatus,
            'startCreatedAt' => $startDate,
            'endCreatedAt' => $endDate,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $merchant = MerchantService::detail(request('id'));
        //增加最后审核时间
        return Result::success($merchant);
    }

    /**
     * 获取审核记录列表
     */
    public function getAuditList()
    {
        $data = MerchantAuditService::getAuditResultList();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取最新一条审核记录
     */
    public function getNewestAuditRecord()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $merchantId = request('id');
        $record = MerchantAuditService::getNewestAuditRecordByMerchantId($merchantId);
        return Result::success($record);
    }

    /**
     * 审核商户
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2,3',
            'audit_suggestion' => 'max:50',
        ]);

        $type = request('type');
        $merchantId = request('id');
        $auditSuggestion = request('audit_suggestion', '');

        $merchant = Merchant::findOrFail($merchantId);
        if(empty($merchant)){
            throw new ParamInvalidException('商户信息不存在');
        }

        // 兼容旧操作, 没有审核记录时创建一条审核记录, 以便于继续走下去
        $merchantCurrentAudit = MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $merchant->audit_oper_id)
            ->whereIn('status', [0,3])
            ->orderBy('updated_at','desc')
            ->first();
        if(empty($merchantCurrentAudit)){
            MerchantAuditService::addAudit($merchantId, $merchant->audit_oper_id);
        }

        switch ($type){
            case '1': // 审核通过
                $merchant = MerchantAuditService::auditSuccess($merchant, $auditSuggestion);
                break;
            case '2': // 审核不通过
                $merchant = MerchantAuditService::auditFail($merchant, $auditSuggestion);
                break;
            case '3': // 审核不通过并打回到商户池
                $merchant = MerchantAuditService::auditFailAndPushToPool($merchant, $auditSuggestion);
                break;
            default:
                throw new BaseResponseException('错误的操作');
        }

        return Result::success($merchant);
    }

    /**
     * 下载Excel
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcel()
    {
        $id = request('merchantId');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $name = request('name');
        $auditStatus = request('auditStatus');
        $signboardName = request('signboardName');
        if ($auditStatus || $auditStatus==="0"){
            $auditStatus = explode(',', $auditStatus);
        }
        $operId = request('operId');
        $operName = request('operName');
        $creatorOperId = request('creatorOperId');
        $creatorOperName = request('creatorOperName');

        return (new MerchantExport($id, $startDate, $endDate,$signboardName, $name,$auditStatus, $operId, $operName, $creatorOperId, $creatorOperName))->download('merchant_list.xlsx');
    }
}