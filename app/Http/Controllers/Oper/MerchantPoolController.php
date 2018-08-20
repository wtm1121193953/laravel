<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/4/24
 * Time: 14:34
 */

namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantPoolService;
use App\Modules\Merchant\MerchantService;
use App\Result;

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
        $operId = request()->get('current_user')->oper_id;
        $data = MerchantPoolService::operMerchantPoolGetList($keyword,$isMine,$operId);
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
        $merchant = MerchantService::detail($id);
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
//            'area_id' => 'required|integer|min:1',   //有些地区，如港澳台没有地区
            'address' => 'required',
        ]);
    }

    /**
     * 添加商户池中的商户信息, 即商户的contract_status为2 并且该商户没有所属运营中心
     */
    public function add()
    {
        $this->formValidate();
        $operId = request()->get('current_user')->oper_id;
        $merchant = MerchantPoolService::operMerchantPoolAdd($operId);

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
        $id = request('id');
        $operId = request()->get('current_user')->oper_id;
        $merchant = MerchantPoolService::operMerchantPoolEdit($id,$operId);
        return Result::success($merchant);
    }
}