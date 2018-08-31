<?php

namespace App\Modules\User;
use Illuminate\Http\Request;
use App\BaseService;
use App\Modules\User\UserIdentityAuditRecord;
use App\Exceptions\BaseResponseException;
use App\ResultCode;

/**
 * 验证记录
 * Author:  Jerry
 * Date:    180831
 * Class UserIdentityAuditRecord
 * @package App\Modules\User
 */
class UserIdentityAuditRecordService extends BaseService
{
    public static function addRecord( Request $request)
    {
        $userIdentityAuditRecord = new UserIdentityAuditRecord;
        $currentUser = $request->get('current_user');
        $userIdentityAuditRecord->name      = $request->input('name');
        $userIdentityAuditRecord->number    = $currentUser->id;
        $userIdentityAuditRecord->number    = $request->input('number');
        $userIdentityAuditRecord->front_pic = $request->input('front_pic');
        $userIdentityAuditRecord->opposite_pic= $request->input('opposite_pic');
        $userIdentityAuditRecord->status    = UserIdentityAuditRecord::STATUS_TO_AUDIT;
        if( $userIdentityAuditRecord->save() )
        {
            throw new BaseResponseException(ResultCode::DB_INSERT_FAIL, '新增失败');
        }
    }
}
