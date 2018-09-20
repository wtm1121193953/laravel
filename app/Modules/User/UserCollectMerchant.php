<?php

namespace App\Modules\User;

use App\BaseModel;

/**
 * 用户收藏店铺
 * Class UserCollectMerchant
 * Author:   JerryChan
 * Date:     2018/9/19 12:11
 * @property int user_id
 * @property int merchant_id
 * @package App\Modules\User
 */
class UserCollectMerchant extends BaseModel
{
    const STATUS_COLLECT = 1;               // 状态：关注
    const STATUS_UNCOLLECT = 2;             // 装填：取消关注
    //
    public function getMerchant()
    {
        return $this->belongsTo('App\Modules\Merchant\Merchant');
    }
}
