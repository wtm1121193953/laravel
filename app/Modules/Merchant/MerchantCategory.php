<?php

namespace App\Modules\Merchant;

use App\BaseModel;

/**
 * Class MerchantCategory
 * @package App\Modules\Merchant
 *
 * @property int    pid
 * @property string name
 * @property string icon
 * @property int status
 * @property int type
 *
 */

class MerchantCategory extends BaseModel
{
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

    /*
     app及小程序首页宫格导航图标类型说明: // type: cs_index-超市首页, merchant_category-某个类目商户列表, merchant_category_all-商户类目列表, h5-h5链接
      */
    const TYPE_CS_INDEX = 1;  // 跳转到超市
    const TYPE_MERCHANT_CATEGORY = 2; //跳转到商户类目
    const TYPE_MERCHANT_CATEGORY_ALL = 3; //跳转到更多
    const TYPE_MERCHANT_CATEGORY_H5 = 4; //跳转到h5页面
}
