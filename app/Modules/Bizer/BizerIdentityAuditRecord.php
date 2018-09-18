<?php

namespace App\Modules\Bizer;

use App\BaseModel;

/**
 * Class BizerIdentityAuditRecord
 * @package App\Modules\Bizer
 * @property integer bizer_id
 * @property string name
 * @property string id_card_no
 * @property string front_pic
 * @property string opposite_pic
 * @property integer status
 * @property string reason
 * @property integer update_user
 */

class BizerIdentityAuditRecord extends BaseModel
{
    /**
     * 状态 1：为待审核，2：为审核通过， 3：为审核失败
     */
    const STATUS_AUDIT_PREPARE = 1;
    const STATUS_AUDIT_SUCCESS = 2;
    const STATUS_AUDIT_FAILED = 3;
    const STATUS_NOT_SUBMIT = 4;     // 不是数据库中的状态 是列表中未提交身份认证的状态
}
