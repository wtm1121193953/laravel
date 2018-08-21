<?php

namespace App\Modules\Wallet;

use App\BaseModel;

/**
 * Class WalletBill
 * @package App\Modules\Wallet
 * @property integer wallet_id
 * @property integer origin_id
 * @property integer origin_type
 * @property string bill_no
 * @property integer type
 * @property integer obj_id
 * @property integer inout_type
 * @property float amount
 * @property integer amount_type
 */

class WalletBill extends BaseModel
{
    /**
     * 钱包流水用户类型
     */
    const ORIGIN_TYPE_USER = 1; // 用户
    const ORIGIN_TYPE_MERCHANT = 2; // 商户
    const ORIGIN_TYPE_OPER = 3; // 运营中心

    /**
     * 钱包流水类型 类型  1-个人消费返利  2-下级消费返利 3-提现  4-个人消费返利退款  5-下级消费返利退款 6-运营中心交易分润 7-运营中心交易分润退款
     */
    const TYPE_SELF = 1;
    const TYPE_SUBORDINATE = 2;
    const TYPE_WITHDRAW = 3;
    const TYPE_SELF_CONSUME_REFUND = 4;
    const TYPE_SUBORDINATE_REFUND = 5;
    const TYPE_OPER = 6;
    const TYPE_OPER_REFUND = 7;

    /**
     * 收支类型 1-收入 2-支出
     */
    const IN_TYPE = 1;
    const OUT_TYPE = 2;

    /**
     * 变动金额类型, 1-冻结金额, 2-非冻结金额
     */
    const AMOUNT_TYPE_FREEZE = 1;
    const AMOUNT_TYPE_UNFREEZE = 2;


}
