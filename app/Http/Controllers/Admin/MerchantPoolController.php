<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/4/24
 * Time: 15:21
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategory;
use App\Modules\Oper\Oper;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class MerchantPoolController extends Controller
{

    /**
     * 获取商户池列表
     */
    public function getList()
    {
        $keyword = request('keyword');
        $data = Merchant::where('audit_oper_id', 0)
            ->when($keyword, function(Builder $query) use ($keyword){
                $query->where('name', 'like', "%$keyword%");
            })
            ->orderByDesc('id')
            ->paginate();
        $data->each(function ($item){
            $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
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
        $merchant->creatorOperName = Oper::where('id', $merchant->creator_oper_id)->value('name');
        return Result::success($merchant);
    }

}