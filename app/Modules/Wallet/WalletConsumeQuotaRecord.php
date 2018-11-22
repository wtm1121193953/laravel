<?php

namespace App\Modules\Wallet;

use App\BaseModel;
use App\Modules\Order\Order;

/**
 * Class WalletConsumeQuotaRecord
 * @package App\Modules\Wallet
 * @property integer wallet_id
 * @property string consume_quota_no
 * @property integer origin_id
 * @property integer origin_type
 * @property integer type
 * @property integer order_id
 * @property string order_no
 * @property float pay_price
 * @property float order_profit_amount
 * @property float consume_quota
 * @property string consume_user_mobile
 * @property integer status
 */

class WalletConsumeQuotaRecord extends BaseModel
{

    /**
     * 消费额记录用户类型
     */
    const ORIGIN_TYPE_USER = 1; // 用户
    const ORIGIN_TYPE_MERCHANT = 2; // 商户
    const ORIGIN_TYPE_OPER = 3; // 运营中心
    const ORIGIN_TYPE_BIZER = 4; // 业务员
    const ORIGIN_TYPE_CS = 5; // 超市

    /**
     * 来源类型 1-消费自返 2-直接下级消费返[下级返时只有积分 其他全部为0]
     */
    const TYPE_SELF = 1;
    const TYPE_SUBORDINATE = 2;

    /**
     * 状态 1-冻结中 2-已解冻待置换 3-已置换 4-已退款 5-置换失败
     */
    const STATUS_FREEZE = 1;
    const STATUS_UNFREEZE = 2;
    const STATUS_REPLACEMENT = 3;
    const STATUS_REFUND = 4;
    const STATUS_FAILED = 5;

    /**
     * 消费额所属订单
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 消费额对应的冻结记录
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function unfreezeRecord()
    {
        return $this->hasOne(WalletConsumeQuotaUnfreezeRecord::class, 'consume_quota_record_id');
    }
}
