<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\User\UserIdentityAuditRecordService;

/**
 * 验证记录
 * Author:  Jerry
 * Date:    180831
 * Class UserIdentityAuditRecord
 * @package App\Http\Controllers\User
 */
class UserIdentityAuditRecord extends Controller
{
    public function addRecord( Request $request )
    {
        $this->validate( $request, [
            'name'      => 'required',
            'number'    => 'required|identitycards',
            'front_pic' => 'required',
            'opposite_pic'=>'required',
        ],[
            'name.required'         => '姓名不可为空',
            'number.required'       => '身份证不可为空',
            'number.identitycards'  => '身份证号码格式错误',
            'front_pic.required'    => '身份证正面照不可缺',
            'opposite_pic.required' => '身份证反面不可缺'
            ]);
        UserIdentityAuditRecordService::addRecord( $request );
    }
}
