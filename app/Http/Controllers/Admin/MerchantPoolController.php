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
use App\Result;

class MerchantPoolController extends Controller
{

    /**
     * 获取商户池列表
     */
    public function getList()
    {
        $data = Merchant::where('audit_oper_id', 0)
            ->orderByDesc('id')->paginate();
        $data->each(function ($item){
            $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
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
        return Result::success($merchant);
    }

}