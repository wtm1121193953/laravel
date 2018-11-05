<?php

namespace App\Modules\User;
use App\BaseModel;

/**
 * 验证记录
 * Author:  Jerry
 * Date:    180831
 * Class UserIdentityAuditRecord
 * @package App\Modules\User
 */

/**
 * Author:  Jerry
 * Date:    180831
 * Class UserIdentityAuditRecord
 * @package App\Modules\User
 * @property integer    user_id
 * @property string    name
 * @property integer country_id
 * @property string    id_card_no
 * @property string     front_pic
 * @property string     opposite_pic
 * @property integer    status
 * @property string     reason
 * @property integer    update_user
 *
 * @property User user
 *
 */
class UserIdentityAuditRecord extends BaseModel
{
    // '状态 1：为待审核，2：为审核通过， 3：为审核失败',  4:为未提交（不存在）
    const STATUS_UN_AUDIT   = 1;
    const STATUS_SUCCESS    = 2;
    const STATUS_FAIL       = 3;
    const STATUS_UN_SAVE    = 4;

    public static function getStatusText($status)
    {
        $status_arr = [self::STATUS_UN_AUDIT=>'待审核',self::STATUS_SUCCESS=>'审核通过',self::STATUS_FAIL=>'审核不通过'];
        return !empty($status_arr[$status])?$status_arr[$status]:'未提交';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
