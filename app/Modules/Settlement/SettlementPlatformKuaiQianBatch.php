<?php
namespace App\Modules\Settlement;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SettlementPlatformKuaiQianBatch
 * @package App\Modules\Settlement
 * @property int type
 * @property int merchant_type
 * @property string batch_no
 * @property string settlement_platfrom_ids
 * @property string data_send
 * @property string data_receive
 * @property string data_query
 * @property float amount
 * @property int total
 * @property int success
 * @property int fail
 * @property int pay_success
 * @property int pay_fail
 * @property int status
 * @property sting create_date
 * @property datetime send_time
 */
class SettlementPlatformKuaiQianBatch extends Model
{
    //
    const STATUS_FAILED = -1;
    const STATUS_NOT_SEND = 0; //未推送
    const STATUS_SENDED = 1; //已推送
    const STATUS_FINISHED = 2; //已完成

    const TYPE_AUTO = 1;
    const TYPE_RE_PAY = 2;

    // 商户类型
    const MERCHANT_TYPE_NORMAL = 1;         // 默认商家
    const MERCHANT_TYPE_SUPERMARKET = 2;    // 超市商家
}
