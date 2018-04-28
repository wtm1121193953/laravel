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
use App\Modules\Oper\Oper;
use App\Result;

class MerchantController extends Controller
{

    public function getList()
    {
        $data = Merchant::where('audit_oper_id', '>', 0)
            ->orderByDesc('updated_at')->paginate();

        $data->each(function ($item){
            $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
            $item->business_time = json_decode($item->business_time, 1);
            $item->operName = Oper::where('id', $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id)->value('name');
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
        $merchant->contract_pic_url = explode(',', $merchant->contract_pic_url);
        $merchant->other_card_pic_urls = explode(',', $merchant->other_card_pic_urls);
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
            'type' => 'required|integer|in:1,3',
        ]);
        //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
        $type = request('type');
        $merchantId = request('id');

        $merchant = Merchant::findOrFail($merchantId);
        $merchantAudit = MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $merchant->audit_oper_id)
            ->first();
        if(empty($merchantAudit)){
            // 兼容旧操作, 没有审核记录时创建一条审核记录, 以便于继续走下去
            $merchantAudit = MerchantAudit::addRecord($merchantId, $merchant->audit_oper_id);
        }

        if($type == 3){
            if($merchant->audit_status == 3){
                throw new ParamInvalidException('当前商户时重新提交审核的商户, 不能打回到商户池');
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
            }
        }
        $merchant->save();
        $merchantAudit->save();
        return Result::success($merchant);
    }
}