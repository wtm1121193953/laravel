<?php

namespace App\Modules\Oper;

use App\BaseModel;

/**
 * Class Oper
 * @package App\Modules\Oper
 *
 * @property string name
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
 */
class Oper extends BaseModel
{
    // 运营中心下的商家是否支付到平台 0-支付给运营中心自己 1-支付到平台(平台不参与分成) 2-支付到平台(平台参与分成)
    const PAY_TO_PLATFORM_NO = 0;
    const PAY_TO_PLATFORM_YES = 1;
    const PAY_TO_PLATFORM_YES2 = 2;
    /**
     * 运营中心下的商家是否支付到平台 0-支付给运营中心自己 1-支付到平台(平台不参与分成) 2-支付到平台(平台参与分成)
     */
    const PAY_TO_OPER = 1;
    const PAY_TO_PLATFORM_WITHOUT_SPLITTING = 2;
    const PAY_TO_PLATFORM_WITH_SPLITTING = 3;
}
