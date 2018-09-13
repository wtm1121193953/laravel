<?php

namespace App\Http\Controllers\Bizer;

use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\MerchantCategoryService;
use App\Result;
use App\Modules\Oper\OperBizerService;

class MerchantController extends Controller
{

    /**
     * 获取业务员的商户列表 (分页)
     */
    public function getList()
    {
        $where_data = [
            'bizer_id'=>request()->get('current_user')->id,//登录所属业务员ID
            'id'=>request('id'),//商户ID
            'operId'=>request('operId'),//运营中心ID
            'name'=>request('merchantName'),//商户名称
            'merchantCategory'=>request('merchant_category'),//商家类别ID   所属行业
            'cityId'=>request('cityId'),// 所在城市id
            'creatorOperId'=>request('creatorOperId'),// 所属运营中心ID
            'startCreatedAt'=>request('startTime'),//添加时间
            'endCreatedAt'=>request('endTime'),//添加时间
            
        ];

        $data = MerchantService::getList($where_data);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
    
    /**
     * 获取所属行业树形
     * @return type
     */
    public function getTree()
    {
        $tree = MerchantCategoryService::getTree();
        return Result::success(['list' => $tree]);
    }
   
    /**
     * 获取业务员所属的运营中心名称
     * @return type
     */
    public function allOperNames(){
        $where_data = [
            'bizer_id'=>request()->get('current_user')->id,//登录所属业务员ID
        ];
        $list = OperBizerService::getBizerOper($where_data);
        return Result::success([
            'list' => $list->items()
        ]);
    }
}