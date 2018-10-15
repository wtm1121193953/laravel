<?php

namespace App\Modules\Wallet;

use App\BaseModel;

/**
 * Class BankCard
 * @package App\Modules\Wallet
 * @property integer origin_id
 * @property integer origin_type
 * @property integer bank_card_type
 * @property string bank_card_open_name
 * @property string bank_card_no
 * @property string bank_name
 * @property string sub_bank_name
 * @property integer default
 */

class BankCard extends BaseModel
{

    /**
     * 分润用户类型, 1-用户 2-商户 3-运营中心
     */
    const ORIGIN_TYPE_USER = 1; // 用户
    const ORIGIN_TYPE_MERCHANT = 2; // 商户
    const ORIGIN_TYPE_OPER = 3; // 运营中心
    const ORIGIN_TYPE_BIZER = 4; // 业务员

    /**
     * 银行账户类型 1-公司账户 2-个人账户
     */
    const BANK_CARD_TYPE_COMPANY = 1;
    const BANK_CARD_TYPE_PEOPLE = 2;

    /**
     * Author:  Jerry
     * Date :   180830
     * 银行卡是否为默认
     */
    const DEFAULT_SELECTED = 1;
    const DEFAULT_UNSELECTED = 0;
}
