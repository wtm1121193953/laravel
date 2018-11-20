<?php

namespace App\Modules\Cs;

use App\BaseModel;

/**
 * Class CsUserAddress
 * @package App\Modules\Cs
 * @property integer id
 * @property string contacts        //联系人
 * @property string contact_phone  //联系电话
 * @property integer user_id        //用户id
 * @property integer province_id    //省id
 * @property string  province       //省名称
 * @property integer city_id        //城市id
 * @property string city            //城市
 * @property integer area_id        //区县id
 * @property string area            //区县名称
 * @property string address         //详细地址
 * @property integer is_default     //是否默认
 */


class CsUserAddress extends BaseModel
{
    /**
     * Author:  zwg
     * Date :   181120
     * 地址是否为默认
     */
    const DEFAULT = 1;
    const UNDEFAULT = 0;
}
