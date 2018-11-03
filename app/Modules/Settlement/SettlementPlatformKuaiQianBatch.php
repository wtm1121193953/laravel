<?php
namespace App\Modules\Settlement;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SettlementPlatformKuaiQianBatch
 * @package App\Modules\Settlement
 * @property string batch_no
 * @property string settlement_platfrom_ids
 * @property string data_send
 * @property string data_receive
 * @property string data_query
 * @property int total
 * @property int success
 * @property int fail
 * @property int pay_success
 * @property int pay_fail
 * @property datetime send_time
 */
class SettlementPlatformKuaiQianBatch extends Model
{
    //
    const STATUS_NOT_SEND = 0; //未推送
    const STATUS_SENDED = 1; //已推送
    const STATUS_FINISHED = 2; //已完成
}
