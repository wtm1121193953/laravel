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
    //
    public function getMerchant()
    {
        return $this->belongsTo('App\Modules\Merchant\Merchant');
    }
}
