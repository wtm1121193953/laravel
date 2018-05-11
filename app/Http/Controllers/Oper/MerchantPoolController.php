<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/4/24
 * Time: 14:34
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategory;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

/**
 * 商户池控制器, 用户获取未签订合同的商户
 * Class MerchantPoolController
 * @package App\Http\Controllers\Oper
 */
class MerchantPoolController extends Controller
{

    public function getList()
    {
        $keyword = request('keyword');
        $isMine = request('isMine');
        $data = Merchant::where('audit_oper_id', 0)
            ->when($keyword, function(Builder $query) use ($keyword){
                $query->where('name', 'like', "%$keyword%");
            })
            ->when($isMine == 1, function(Builder $query) {
                $query->where('creator_oper_id', request()->get('current_user')->oper_id);
            })
            ->orderBy('id', 'desc')
            ->paginate();

        $data->each(function ($item){
            if ($item->merchant_category_id){
                $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
            }
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'mine' => $isMine
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $merchant = Merchant::findOrFail($id);
        if ($merchant->merchant_category_id){
            $merchant->categoryPath = MerchantCategory::getCategoryPath($merchant->merchant_category_id);
        }
        $merchant->desc_pic_list = $merchant->desc_pic_list ? explode(',', $merchant->desc_pic_list) : [];
        return Result::success($merchant);
    }

    /**
     * 基础表单验证, 用于添加及编辑
     */
    protected function formValidate()
    {
        $this->validate(request(), [
            'name' => 'required',
            'merchant_category_id' => 'required|integer|min:1',
            'lng' => 'required|numeric',
            'lat' => 'required|numeric',
            'province_id' => 'required|integer|min:1',
            'city_id' => 'required|integer|min:1',
            'area_id' => 'required|integer|min:1',
            'address' => 'required',
        ]);
    }

    /**
     * 添加商户池中的商户信息, 即商户的contract_status为2 并且该商户没有所属运营中心
     */
    public function add()
    {
        $this->formValidate();

        $merchant = new Merchant();
        $merchant->fillMerchantPoolInfoFromRequest();

        // 商户名不能重复
        $exists = Merchant::where('name', $merchant->name)->first();
        if($exists){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchant->creator_oper_id = request()->get('current_user')->oper_id;
        $merchant->save();
        return Result::success($merchant);
    }

    /**
     * 修改商户池中的商户信息
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $this->formValidate();

        $merchant = Merchant::findOrFail(request('id'));
        if($merchant->creator_oper_id != request()->get('current_user')->oper_id){
            throw new ParamInvalidException('不能修改其他运营中心录入的商户资料');
        }
        $merchant->fillMerchantPoolInfoFromRequest();

        // 商户名不能重复
        $exists = Merchant::where('name', $merchant->name)->first();
        if($exists){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchant->save();
        return Result::success($merchant);
    }
}