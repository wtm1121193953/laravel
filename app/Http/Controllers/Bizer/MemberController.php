<?php

namespace App\Http\Controllers\Bizer;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Bizer\BizermerchantMember;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class MemberController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        
        $oper_id = request()->get('current_user')->oper_id;
        $where_data['id'] = '';
        $where_data['operId']= $oper_id;
        $where_data['name']= request('merchantName');//商户名称
        $where_data['signboardName']= '';
        $where_data['status']= '';
        $where_data['auditStatus']= '';
        $where_data['merchantCategory']= '';
        $where_data['isPilot']= '';
        $where_data['startCreatedAt']= request('startTime');
        $where_data['endCreatedAt']= request('endTime');
        $where_data["city"] = request('city');// 所在城市
        $where_data["creatorOperId"] = request('creatorOperId');// 所在城市
        
//        $data = Merchant::where(function (Builder $query){
//            $query->where('oper_id', request()->get('current_user')->oper_id)
//                ->orWhere('audit_oper_id',  request()->get('current_user')->oper_id);
//        })->where('oper_biz_member_code', 'T789411');
//                
//        if($startTime){
//            // $data = $data->where();
//        }
//                
//        $data = $data->select('id', 'active_time', 'name', 'status','audit_status','created_at')->paginate();
        $data = MerchantService::getList($where_data);
        $data->each(function($item) {
            //$auditStatusArray = ['待审核','已审核','审核不通过','重新提交审核'];
            //      0-待审核 1-已审核 2-审核不通过 3-重新提交审核'
           // $item->audit_done_time = $item->audit_status==1 ? $item->active_time : $auditStatusArray[$item->audit_status];
        });
        //print_r($data->items());exit;
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

  

}