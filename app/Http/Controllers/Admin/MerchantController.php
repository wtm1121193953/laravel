<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 15:34
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategory;
use App\Result;

class MerchantController extends Controller
{

    public function getList()
    {
        $data = Merchant::orderByDesc('id')->paginate();

        $data->each(function ($item){
            $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
            $item->business_time = json_decode($item->business_time, 1);
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
        return Result::success($merchant);
    }

    /**
     * 审核商户
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'audit_status' => 'required|integer|in:1,2',
        ]);
        $merchant = Merchant::findOrFail(request('id'));
        $merchant->audit_status = request('audit_status');
        $merchant->save();
        return Result::success($merchant);
    }
}