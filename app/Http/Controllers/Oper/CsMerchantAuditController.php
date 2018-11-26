<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\ParamInvalidException;
use App\Modules\Cs\CsMerchant;
use App\Modules\Cs\CsMerchantAuditService;
use App\Modules\Cs\CsMerchantService;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CsMerchantAuditController extends Controller
{

    /**
     * 添加或者修改审核记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function editOrAdd(){
        $validate = [
            'name' => 'required|max:50',
            'signboard_name' => 'required|max:20',
        ];
        $validate = array_merge($validate, [
            'business_licence_pic_url' => 'required',
            'organization_code' => 'required',
            'settlement_rate' => 'required|numeric|min:0',
        ]);
        $this->validate(request(), $validate);
        $mobile = request('contacter_phone');
        if(!preg_match('/^1[3,4,5,6,7,8,9]\d{9}$/', $mobile)){
            throw new ParamInvalidException('负责人手机号码不合法');
        }
        $id = request()->get('id',0);
        $dataType= request()->get('dataType');  // 用以区分修改来源类型
        $audit = CsMerchantAuditService::editOrAddMerchantAudit($id,request()->get('current_user')->id,$dataType);
        return Result::success($audit);
    }
}
