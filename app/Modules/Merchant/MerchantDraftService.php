<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/9
 * Time: 16:46
 */

namespace App\Modules\Merchant;

use App\BaseService;
use App\Exceptions\ParamInvalidException;
use App\Modules\Oper\OperBizMember;
use Illuminate\Database\Eloquent\Builder;

/**
 * 商户草稿箱相关service
 * Class MerchantDraftService
 * @package App\Modules\Merchant
 */
class MerchantDraftService extends BaseService
{
    /**
     * 根据ID获取商户信息
     * @param $merchantId
     * @param array|string $fields
     * @return MerchantDraft
     */
    public static function getById($merchantId, $fields = ['*'])
    {
        if(is_string($fields)){
            $fields = explode(',', $fields);
        }
        return MerchantDraft::find($merchantId, $fields);
    }

    public static function getList($currentOperId,$status,$auditStatus,$name)
    {
        $data = MerchantDraft::where(function (Builder $query) use ($currentOperId){

            $query->where('oper_id', $currentOperId)
                ->orWhere('audit_oper_id', $currentOperId);
        })
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when(!empty($auditStatus), function (Builder $query) use ($auditStatus){
                if($auditStatus == -1){
                    $auditStatus = 0;
                }
                $query->where('audit_status', $auditStatus);
            })
            ->when($name, function (Builder $query) use ($name){
                $query->where('name', 'like', "%$name%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate();

        $data->each(function ($item){
            if ($item->merchant_category_id){
                $item->categoryPath = MerchantCategoryService::getCategoryPath($item->merchant_category_id);
            }
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
            $item->account = MerchantAccount::where('merchant_id', $item->id)->first();
            $item->operBizMemberName = OperBizMember::where('oper_id', $item->oper_id)->where('code', $item->oper_biz_member_code)->value('name') ?: '无';
        });

        return $data;
    }

    public static function detail($id)
    {
        $merchantDraft = MerchantDraft::findOrFail($id);
        $merchantDraft->categoryPath = $merchantDraft->merchant_category_id ? MerchantCategoryService::getCategoryPath($merchantDraft->merchant_category_id) : [];
        $merchantDraft->categoryPathOnlyEnable = $merchantDraft->merchant_category_id ? MerchantCategoryService::getCategoryPath($merchantDraft->merchant_category_id, true) : [];
        $merchantDraft->account = MerchantAccount::where('merchant_id', $merchantDraft->id)->first();

        return $merchantDraft;
    }

    public static function add($currentOperId)
    {
        $merchantDraft = new MerchantDraft();
        $merchantDraft->fillMerchantPoolInfoFromRequest();
        $merchantDraft->fillMerchantActiveInfoFromRequest();

        // 补充商家创建者及审核提交者
        $merchantDraft->audit_oper_id = $currentOperId;
        $merchantDraft->creator_oper_id = $currentOperId;

        // 商户名不能重复
        $exists = Merchant::where('name', $merchantDraft->name)->first();
        $existsDraft = MerchantDraft::where('name', $merchantDraft->name)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('商户名称不能重复');
        }
        // 招牌名不能重复
        $exists = Merchant::where('signboard_name', $merchantDraft->signboard_name)->first();
        $existsDraft = MerchantDraft::where('signboard_name', $merchantDraft->signboard_name)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('招牌名称不能重复');
        }

        $merchantDraft->save();

        return $merchantDraft;
    }

    public static function edit($id,$currentOperId)
    {
        $merchantDraft = MerchantDraft::where('id', $id)
            ->where('audit_oper_id', $currentOperId)
            ->firstOrFail();

        $merchantDraft->fillMerchantPoolInfoFromRequest();
        $merchantDraft->fillMerchantActiveInfoFromRequest();

        // 商户名不能重复
        $exists = Merchant::where('name', $merchantDraft->name)->first();
        $existsDraft = MerchantDraft::where('name', $merchantDraft->name)
            ->where('id', '<>', $merchantDraft->id)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('商户名称不能重复');
        }
        // 招牌名不能重复
        $exists = Merchant::where('signboard_name', $merchantDraft->signboard_name)->first();
        $existsDraft = MerchantDraft::where('signboard_name', $merchantDraft->signboard_name)
            ->where('id', '<>', $merchantDraft->id)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('招牌名称不能重复');
        }

        $merchantDraft->save();

        return $merchantDraft;
    }

}