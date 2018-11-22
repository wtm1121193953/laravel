<?php

namespace App\Modules\FeeSplitting;

use App\BaseModel;

/**
 * Class FeeSplittingRecord
 * @package App\Modules\FeeSplitting
 *
 * @property integer origin_id
 * @property integer origin_type
 * @property integer merchant_level
 * @property integer order_id
 * @property string order_no
 * @property float order_profit_amount
 * @property float ratio
 * @property float amount
 * @property integer type
 * @property integer status
 */
class FeeSplittingRecord extends BaseModel
{
    //
    const TYPE_TO_SELF = 1; // 自返
    const TYPE_TO_PARENT = 2; // 返利给上级
    const TYPE_TO_OPER = 3; // 返利给运营中心
    const TYPE_TO_BIZER = 4; // 返利给业务员

    // 用户类型  1-用户
    const ORIGIN_TYPE_USER = 1;
    // 用户类型  2-商户
    const ORIGIN_TYPE_MERCHANT = 2;
    // 用户类型  3-运营中心
    const ORIGIN_TYPE_OPER = 3;
    // 用户类型  4-业务员
    const ORIGIN_TYPE_BIZER = 4;
    // 用户类型  5-超市
    const ORIGIN_TYPE_CS = 5;

    // 状态 冻结中
    const STATUS_FREEZE = 1;
    // 状态 已解冻
    const STATUS_UNFREEZE = 2;
    // 状态 已退款退回
    const STATUS_REFUNDED = 3;
}
