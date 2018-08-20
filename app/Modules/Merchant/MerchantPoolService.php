<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/16
 * Time: 18:04
 */

namespace App\Modules\Merchant;


use App\BaseService;
use App\Exceptions\ParamInvalidException;
use App\Modules\Oper\Oper;
use Illuminate\Database\Eloquent\Builder;

class MerchantPoolService extends BaseService
{

    /**
     * 获取商户池列表
     * @param $keyword string
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($keyword = '')
    {
        //
        $data = Merchant::where('audit_oper_id', 0)
            ->when($keyword, function (Builder $query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%");
            })
            ->orderByDesc('id')
            ->paginate();
        $data->each(function ($item) {
            $item->categoryPath = MerchantCategoryService::getCategoryPath($item->merchant_category_id);
            $item->creatorOperName = Oper::where('id', $item->creator_oper_id)->value('name');
        });

        return $data;
    }

    /**
     * 获取商户池详情
     * @param $id
     * @return Merchant
     */
    public static function detail($id)
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->categoryPath = MerchantCategoryService::getCategoryPath($merchant->merchant_category_id);
        $merchant->creatorOperName = Oper::where('id', $merchant->creator_oper_id)->value('name');
        $oper = Oper::where('id', $merchant->oper_id > 0 ? $merchant->oper_id : $merchant->audit_oper_id)->first();
        if ($oper) {
            $merchant->operAddress = $oper->province . $oper->city . $oper->area . $oper->address;
        }

        return $merchant;
    }

    public static function add()
    {
        // todo 添加商户信息到商户池
    }

    public static function edit()
    {
        // todo  编辑商户池信息
    }

    /**
     * 商户池控制器, 用户获取未签订合同的商户
     * @param $keyword
     * @param $isMine
     * @param $operId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function operMerchantPoolGetList($keyword,$isMine,$operId){

        $data = Merchant::where('audit_oper_id', 0)
            ->when($keyword, function(Builder $query) use ($keyword){
                $query->where('name', 'like', "%$keyword%");
            })
            ->when($isMine == 1, function(Builder $query) use ($operId) {
                $query->where('creator_oper_id', $operId);
            })
            ->orderBy('id', 'desc')
            ->paginate();

        $data->each(function ($item){
            if ($item->merchant_category_id){
                $item->categoryPath = MerchantCategoryService::getCategoryPath($item->merchant_category_id);
            }
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
        });

        return $data;
    }

    /**
     * 添加商户池中的商户信息, 即商户的contract_status为2 并且该商户没有所属运营中心
     * @param $operId
     * @return Merchant
     */
    public static function operMerchantPoolAdd($operId)
    {
        $merchant = new Merchant();
        $merchant->fillMerchantPoolInfoFromRequest();

        // 商户名不能重复
        $exists = Merchant::where('name', $merchant->name)->first();
        if($exists){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchant->creator_oper_id = $operId;
        $merchant->save();

        return $merchant;
    }

    /**
     * 修改商户池中的商户信息
     * @param $id
     * @param $operId
     * @return Merchant
     */
    public static function operMerchantPoolEdit($id,$operId){
        $merchant = Merchant::findOrFail($id);
        if($merchant->creator_oper_id != $operId){
            throw new ParamInvalidException('不能修改其他运营中心录入的商户资料');
        }
        $merchant->fillMerchantPoolInfoFromRequest();

        // 商户名不能重复
        $exists = Merchant::where('name', $merchant->name)
            ->where('id', '<>', $merchant->id)->first();
        if($exists){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchant->save();

        return $merchant;
    }
}