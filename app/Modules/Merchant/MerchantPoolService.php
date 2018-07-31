<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/16
 * Time: 18:04
 */

namespace App\Modules\Merchant;


use App\BaseService;
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
}