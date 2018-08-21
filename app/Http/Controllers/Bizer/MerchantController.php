<?php

namespace App\Http\Controllers\Bizer;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Bizer\BizermerchantMember;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Oper\OperService;

class MerchantController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        
        $where_data['bizer_id'] = request()->get('current_user')->id;//登录所属业务员ID
        $where_data['id'] = request('id');;//商户ID
        $where_data['operId']= request('operId');//运营中心ID
        $where_data['name']= request('merchantName');//商户名称
       // $where_data['signboardName']= '';//商家招牌名称
       // $where_data['status']= '';//状态 1-正常 2-禁用
       // $where_data['auditStatus']= '';//商户资料审核状态 0-未审核 1-已审核 2-审核不通过 3-重新提交审核
        $where_data['merchantCategory']= request('merchant_category');//商家类别ID   所属行业
       // $where_data['isPilot']= '';//是否是试点商户 0普通商户 1试点商户
        $where_data['startCreatedAt']= request('startTime');//添加时间
        $where_data['endCreatedAt']= request('endTime');//添加时间
        $where_data["cityId"] = request('cityId');// 所在城市id
        $where_data["creatorOperId"] = request('creatorOperId');// 所属运营中心ID
       // print_r($where_data);exit;
        $data = MerchantService::getList($where_data);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
    
    public function getTree()
    {
        $tree = MerchantCategoryService::getTree();
        return Result::success(['list' => $tree]);
    }
    /**
     * 获取全部的商户名称
     */
    public function allNames()
    {
        $data = [
            'audit_status' => request('audit_status'),
            'status' => request('status'),
        ];
        $list = MerchantService::getAllNames($data);
        return Result::success([
            'list' => $list
        ]);
    }
    public function allOperNames(){
        $data_where["status"] =1;//获取所有正常状态的运营中心
        $list = OperService::getAll($data_where,["id","name"]);
        return Result::success([
            'list' => $list
        ]);
    }

  

}