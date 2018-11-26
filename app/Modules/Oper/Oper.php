<?php

namespace App\Modules\Oper;

use App\BaseModel;

/**
 * Class Oper
 * @package App\Modules\Oper
 *
 * @property string name
 * @property string number
 * @property string tel
 * @property string province
 * @property string city
 * @property string area
 * @property string address
 * @property string email
 * @property string legal_name
 * @property string legal_id_card
 * @property number invoice_type
 * @property number invoice_tax_rate
 * @property number settlement_cycle_type
 * @property string bank_card_no
 * @property string sub_bank_name
 * @property string bank_open_name
 * @property string bank_open_address
 * @property string bank_code
 * @property string licence_pic_url
 * @property number mapping_user_id
 * @property number pay_to_platform
 * @property number status
 * @property float bizer_divide
 * @property string contact_qq
 * @property string contact_wechat
 * @property string contact_mobile
 * @property string business_licence_pic_url
 * @property number city_id
 * @property number province_id
 * @property string contacter
 */
class Oper extends BaseModel
{
    /**
     * 运营中心下的商家是否支付到平台 0-支付给运营中心自己 1-支付到平台(平台不参与分成) 2-支付到平台(平台参与分成)
     */
    const PAY_TO_OPER = 0;
    const PAY_TO_PLATFORM_WITHOUT_SPLITTING = 1;
    const PAY_TO_PLATFORM_WITH_SPLITTING = 2;

    const STATUS_NORMAL = 1;
    const STATUS_FROZEN = 2;
    CONST STATUS_STOP = 3;
}
