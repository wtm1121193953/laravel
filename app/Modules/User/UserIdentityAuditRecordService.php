<?php

namespace App\Modules\User;
use App\Result;
use Illuminate\Http\Request;
use App\BaseService;
use App\Modules\User\UserIdentityAuditRecord;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\ResultCode;

/**
 * 身份验证记录
 * Author:  Jerry
 * Date:    180831
 * Class UserIdentityAuditRecord
 * @package App\Modules\User
 */
class UserIdentityAuditRecordService extends BaseService
{
    /**
     * 新增
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     */
    public static function addRecord( Request $request )
    {
        $userIdentityAuditRecord = new UserIdentityAuditRecord;
        $currentUser = $request->get('current_user');
        $userIdentityAuditRecord->name      = $request->input('name');
        $userIdentityAuditRecord->number    = $request->input('number');
        $userIdentityAuditRecord->front_pic = $request->input('front_pic');
        $userIdentityAuditRecord->opposite_pic= $request->input('opposite_pic');
        $userIdentityAuditRecord->status    = UserIdentityAuditRecord::STATUS_TO_AUDIT;
        $userIdentityAuditRecord->user_id   = $currentUser->id;
        if( !$userIdentityAuditRecord->save() )
        {
            throw new BaseResponseException(ResultCode::DB_INSERT_FAIL, '新增失败');
        }
    }

    /**
     * 修改
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     */
    public static function modRecord( Request $request )
    {
        $currentUser = $request->get('current_user');
        // 判断用户数据是否存在
        $record = UserIdentityAuditRecord::where('user_id', $currentUser->id )
                                        ->where('id', $request->input('id') )
                                        ->first();
        if( !$record )
        {
            throw new DataNotFoundException( '找不到用户验证信息' );
        }
        // 判断是否有新修改内容
        if( count( $request->all() )<=1 )
        {
            throw new BaseResponseException(ResultCode::DB_UPDATE_FAIL, '不可无修改内容');
        }
        foreach ( $request->all() as $k=>$v ){
            $record->$k = $v;
        }
        $record->status    = UserIdentityAuditRecord::STATUS_TO_AUDIT;
        if( !$record->save() )
        {
            throw new BaseResponseException(ResultCode::DB_UPDATE_FAIL, '修改失败');
        }
    }

}
