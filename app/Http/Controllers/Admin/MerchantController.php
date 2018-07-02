<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 15:34
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAudit;
use App\Modules\Merchant\MerchantCategory;
use App\Modules\Merchant\MerchantExport;
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
        $auditStatus = request('auditStatus');
        if($auditStatus[0]==null){
            $auditStatus=["0","1","2","3"];
        }
        $operId = request('operId');
        $operName = request('operName');
        $operIds = [];
        if($operName) {
            $result = Oper::where('name', 'like', "%$operName%")->get();
            if (!$result->isEmpty()){
                foreach ($result as $k => $v) {
                    $operIds[$k] = $v->id;
                }
            }else{
                $operIds=[0.000001];
            }
        }

        $creatorOperId = request('creatorOperId');
        $creatorOperName = request('creatorOperName');
        $createOperIds=[];
        if($creatorOperName){
            $createResult = Oper::where('name', 'like', "%$creatorOperName%")->get();
            if(!$createResult->isEmpty()){
                foreach ($createResult as $k=>$v){
                    $createOperIds[$k]=$v->id;
                }
            }else{
                $createOperIds=[0.000001];
            }
        }
        $data = Merchant::where('audit_oper_id', '>', 0)
            ->when($id, function (Builder $query) use ($id){
                $query->where('id', $id);
            })->when($operId, function (Builder $query) use ($operId){
                $query->where('oper_id', $operId);
            })->when($creatorOperId, function (Builder $query) use ($creatorOperId){
                $query->where('creator_oper_id', $creatorOperId);
            })->when($operIds, function (Builder $query) use ($operIds){
                $query->whereIn('oper_id', $operIds);
            })->when($createOperIds, function (Builder $query) use ($createOperIds){
                $query->whereIn('creator_oper_id', $createOperIds);
            })
            ->when($startDate, function (Builder $query) use ($startDate){
                $query->where('created_at', '>=', $startDate.' 00:00:00');
            })
            ->when($endDate, function (Builder $query) use ($endDate){
                $query->where('created_at', '<=', $endDate.' 23:59:59');
            })
            ->when(!empty($auditStatus) && isset($auditStatus), function (Builder $query) use ($auditStatus){

                    $query->whereIn('audit_status', $auditStatus);
            })
            ->when($name, function (Builder $query) use ($name){
                $query->where('name', 'like', "%$name%");
            })
            ->orderByDesc('id')->paginate();

        $data->each(function ($item){
            $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
            $item->business_time = json_decode($item->business_time, 1);
            $item->operName = Oper::where('id', $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id)->value('name');
            $item->operID =$item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id;
            $item->creatorOperId =  $item->creator_oper_id;
            $item->creatorOperName = Oper::where('id', $item->creator_oper_id)->value('name');

        });

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
        $merchant->desc_pic_list = explode(',', $merchant->desc_pic_list);
        $merchant->contract_pic_url = explode(',', $merchant->contract_pic_url);
        $merchant->other_card_pic_urls = explode(',', $merchant->other_card_pic_urls);
        if($merchant->oper_biz_member_code){
            $merchant->operBizMemberName = OperBizMember::where('code', $merchant->oper_biz_member_code)->value('name');
        }
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
        ])
            ->orderByDesc('updated_at')
            ->paginate();
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
     * 审核商户
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2,3',
            'audit_suggestion' =>  'max:50',
        ]);
        //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
        $type = request('type');
        $merchantId = request('id');
        $auditSuggestion = request('audit_suggestion');

        $merchant = Merchant::findOrFail($merchantId);
        $merchantAudit = MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $merchant->audit_oper_id)
            ->first();
        if(empty($merchantAudit)){
            // 兼容旧操作, 没有审核记录时创建一条审核记录, 以便于继续走下去
            $merchantAudit = MerchantAudit::addRecord($merchantId, $merchant->audit_oper_id);
        }

        if($type == 3){
            if($merchant->oper_id > 0){
                throw new ParamInvalidException('该商户已有所属运营中心, 不能打回商户池');
            }
            $merchant->audit_status = Merchant::AUDIT_STATUS_FAIL;
            // 打回商户池操作, 需要将商户信息中的audit_oper_id置空
            $merchant->audit_oper_id = 0;
            $merchantAudit->status = Merchant::AUDIT_STATUS_FAIL_TO_POOL;
        }else {
            $merchant->audit_status = $type;
            $merchantAudit->status = $type;
            if($type == 1){
                // 如果审核通过, 补充商户所属运营中心ID
                $merchant->oper_id = $merchant->audit_oper_id;
                // 如果商户首次激活时间为空, 补充商户首次激活时间
                if(empty($merchant->active_time)){
                    $merchant->active_time = new Carbon();
                }
            }
        }
        $merchant->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';

        $merchant->save();
        $merchantAudit->save();
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

        return (new MerchantExport($id, $startDate, $endDate, $name, $auditStatus))->download('merchant_list.xlsx');
    }
}