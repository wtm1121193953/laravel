<?php

namespace App\Modules\Wallet;

use App\BaseModel;

/**
 * Class WalletWithdraw
 * @package App\Modules\Wallet
 * @property integer wallet_id
 * @property integer origin_id
 * @property integer origin_type
 * @property string withdraw_no
 * @property float amount
 * @property float charge_amount
 * @property float remit_amount
 * @property integer status
 * @property string invoice_express_company
 * @property string invoice_express_no
 * @property integer bank_card_type
 * @property string bank_card_open_name
 * @property string bank_card_no
 * @property string bank_name
 */

class WalletWithdraw extends BaseModel
{

    /**
     * 钱包提现 用户类型
     */
    const ORIGIN_TYPE_USER = 1; // 用户
    const ORIGIN_TYPE_MERCHANT = 2; // 商户
    const ORIGIN_TYPE_OPER = 3; // 运营中心

    /**
     * 状态 1-提现中 2-提现成功 3-提现失败
     */
    const STATUS_WITHDRAWING = 1;
    const STATUS_WITHDRAW_SUCCESS = 2;
    const STATUS_WITHDRAW_FAILED = 3;

    /**
     * 账户类型 1-公司 2-个人
     */
    const BANK_CARD_TYPE_COMPANY = 1;
    const BANK_CARD_TYPE_PEOPLE = 2;
}
