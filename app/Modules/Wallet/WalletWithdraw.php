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
 * @property integer batch_id
 * @property string batch_no
 * @property string remark
 */

class WalletWithdraw extends BaseModel
{

    /**
     * 钱包提现 用户类型
     */
    const ORIGIN_TYPE_USER = 1; // 用户
    const ORIGIN_TYPE_MERCHANT = 2; // 商户
    const ORIGIN_TYPE_OPER = 3; // 运营中心
    const ORIGIN_TYPE_BIZER = 4; // 业务员
    const ORIGIN_TYPE_CS = 5; // 超市

    /**
     * 状态 1-审核中 2-审核通过 3-已打款 4-打款失败 5-审核不通过
     */
    const STATUS_AUDITING = 1;
    const STATUS_AUDIT = 2;
    const STATUS_WITHDRAW = 3;
    const STATUS_WITHDRAW_FAILED = 4;
    const STATUS_AUDIT_FAILED = 5;

    /**
     * 账户类型 1-公司 2-个人
     */
    const BANK_CARD_TYPE_COMPANY = 1;
    const BANK_CARD_TYPE_PEOPLE = 2;
}
