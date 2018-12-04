<?php

namespace App\Modules\User;

use App\BaseService;
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
     * @param array                  $data
     * @param \App\Modules\User\User $user
     */
    public static function addRecord($data, $user)
    {
        $userIdentityAuditRecord = new UserIdentityAuditRecord;
        $userIdentityAuditRecord->name = $data['name'];
        $userIdentityAuditRecord->country_id = $data['country_id'];
        $userIdentityAuditRecord->id_card_no = $data['id_card_no'];
        $userIdentityAuditRecord->front_pic = $data['front_pic'];
        $userIdentityAuditRecord->opposite_pic = $data['opposite_pic'];
        $userIdentityAuditRecord->status = UserIdentityAuditRecord::STATUS_UN_AUDIT;
        $userIdentityAuditRecord->user_id = $user->id;
        $userIdentityAuditRecord ->reason = '';
        if (!$userIdentityAuditRecord->save()) {
            throw new BaseResponseException(ResultCode::DB_INSERT_FAIL, '新增失败');
        }
    }


    /**
     * 修改
     * Author:  Jerry
     * Date:    180831
     * @param array                  $data
     * @param \App\Modules\User\User $user
     */
    public static function modRecord($data, $user)
    {
        // 判断用户数据是否存在
        $record = UserIdentityAuditRecord::where('user_id', $user->id)
            ->where('status', UserIdentityAuditRecord::STATUS_FAIL)
            ->first();
        if (!$record) {
            throw new DataNotFoundException('找不到可修改的用户验证信息');
        }
        // 判断身份证是否被他人使用
//        self::checkRecordCardNoUsed($user->id, $data['id_card_no']);
        // 判断是否有新修改内容
        if (count($data) <= 1) {
            throw new BaseResponseException(ResultCode::DB_UPDATE_FAIL, '不可无修改内容');
        }
        $record->name = $data['name'];
        $record->country_id = $data['country_id'];
        $record->id_card_no = $data['id_card_no'];
        $record->front_pic = $data['front_pic'];
        $record->opposite_pic = $data['opposite_pic'];
        $record->status = UserIdentityAuditRecord::STATUS_UN_AUDIT;

        if (!$record->save()) {
            throw new BaseResponseException(ResultCode::DB_UPDATE_FAIL, '修改失败');
        }
    }

    /**
     * 获取用户的身份审核记录
     * @param $userId
     * @return UserIdentityAuditRecord
     */
    public static function getRecordByUserId($userId)
    {
        $record = UserIdentityAuditRecord::where('user_id', $userId)->first();
        return $record;
    }


    /**
     * Author:  Jerry
     * Date:    180904
     * 判断是别的用户是否已绑定改身份证
     * @param $userId
     * @param $cardNo
     */
    public static function checkRecordCardNoUsed( $userId, $cardNo)
    {
        $exist=  UserIdentityAuditRecord::where('id_card_no', $cardNo)
                    ->where('user_id', '!=', $userId)->exists();
        if( $exist )
        {
            throw new BaseResponseException( '该身份证号已被他人使用!',ResultCode::PARAMS_INVALID);
        }

    }

}
