<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 15:34
 */

namespace App\Http\Controllers\Admin;


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
        $operName = request('operName');
        if($operName) {
            $operIds = Oper::where('name', 'like', "%$operName%")
                ->select('id')->get()
                ->pluck('id');
        }

        $creatorOperId = request('creatorOperId');
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
        $merchant = Merchant::findOrFail(request('id'));
        $merchant->categoryPath = MerchantCategory::getCategoryPath($merchant->merchant_category_id);
        $merchant->business_time = json_decode($merchant->business_time, 1);
        $merchant->operName = Oper::where('id', $merchant->oper_id > 0 ? $merchant->oper_id : $merchant->audit_oper_id)->value('name');
        $merchant->creatorOperName = Oper::where('id', $merchant->creator_oper_id)->value('name');
        $merchant->desc_pic_list = $merchant->desc_pic_list ? explode(',', $merchant->desc_pic_list) : '';
        $merchant->contract_pic_url = $merchant->contract_pic_url ? explode(',', $merchant->contract_pic_url) : '';
        $merchant->other_card_pic_urls = $merchant->other_card_pic_urls ? explode(',', $merchant->other_card_pic_urls) : '';
        $merchant->bank_card_pic_a = $merchant->bank_card_pic_a ? explode(',', $merchant->bank_card_pic_a) : '';
        if($merchant->oper_biz_member_code){
            $merchant->operBizMemberName = OperBizMember::where('code', $merchant->oper_biz_member_code)->value('name');
        }
        $oper = Oper::where('id', $merchant->oper_id > 0 ? $merchant->oper_id : $merchant->audit_oper_id)->first();
        if ($oper){
            $merchant->operAddress = $oper->province.$oper->city.$oper->area.$oper->address;
        }
        //增加最后审核时间
        return Result::success($merchant);
    }

    /**
     * 获取审核记录列表
     */
    public function getAuditList()
    {
        $data = MerchantAudit::whereIn('status', [
            Merchant::AUDIT_STATUS_SUCCESS,
            Merchant::AUDIT_STATUS_FAIL,
            Merchant::AUDIT_STATUS_FAIL_TO_POOL,
        ]) ->orderByDesc('updated_at')->paginate();


        $data->each(function($item) {
            $item->merchantName = Merchant::where('id', $item->merchant_id)->value('name');
            $item->operName = Oper::where('id', $item->oper_id)->value('name');
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取最新审核记录
     */
    public function getNewAuditList()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $merchantId = request('id');
        $merchant = Merchant::findOrFail(request('id'));
        $data = MerchantAudit::where("merchant_id",$merchantId)
            ->where('status',"<>",0)
            ->orderByDesc('updated_at')
            ->first();

        $data->categoryName= MerchantCategory::where("id",$merchant->merchant_category_id)->value("name");
        $data->merchantName = Merchant::where('id', $merchantId)->value('name');
        return Result::success($data);

    }

    /**
     * 审核商户
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2,3',
            'audit_suggestion' =>  'max:50',
        ]);

        $type = request('type');
        $merchantId = request('id');
        $auditSuggestion = request('audit_suggestion');
        $merchant = Merchant::findOrFail($merchantId);
        $merchantCurrentAudit = MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $merchant->audit_oper_id)
            ->whereIn('status', [0,3])
            ->orderBy('updated_at','desc')
            ->first();
        if(empty($merchantCurrentAudit)){
            // 兼容旧操作, 没有审核记录时创建一条审核记录, 以便于继续走下去
            $merchantCurrentAudit = MerchantAuditService::addAudit($merchantId, $merchant->audit_oper_id);
        }

         //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
        if($type == 3){

            if($merchant->oper_id > 0){
                throw new ParamInvalidException('该商户已有所属运营中心, 不能打回商户池');
            }

            $merchant->audit_status = Merchant::AUDIT_STATUS_FAIL;
            // 打回商户池操作, 需要将商户信息中的audit_oper_id置空
            $merchant->audit_oper_id = 0;
            $merchantCurrentAudit->status = Merchant::AUDIT_STATUS_FAIL_TO_POOL;
        }else {
            $merchant->audit_status = $type;
            $merchantCurrentAudit->status = $type;
            if($type == 1){
                // 如果审核通过, 补充商户所属运营中心ID
                $merchant->oper_id = $merchant->audit_oper_id;
                $merchant->active_time = new Carbon();
            }
            $merchant->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';
            $merchantCurrentAudit->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';

        }
        $merchantCurrentAudit->save();
        $merchant->save();
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