<?php

namespace App\Http\Controllers\User;

use App\Result;
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
class UserIdentityAuditRecordController extends Controller
{
    public $validator;
    public function __construct()
    {
        $this->validator = new \App\Validator\User\UserIdentityAuditRecord;
    }

    /**
     * Author:  Jerry
     * Date:    180831
     * 新增身份验证记录
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addRecord( Request $request )
    {
        $data = $request->all();
        $data['user_id'] = $request->get('current_user')->id;
        $this->validator->scene('add')->check( $data );
        UserIdentityAuditRecordService::addRecord( $request );
        return Result::success('提交成功');
    }

    /**
     * Author:  Jerry
     * Date:    180831
     * 修改身份验证记录
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function modRecord( Request $request )
    {
        $this->validator->scene('mod')->check( $request->all() );
        UserIdentityAuditRecordService::modRecord( $request );
        return Result::success('修改成功');
    }
}
