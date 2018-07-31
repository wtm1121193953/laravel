<?php

namespace App\Modules\Goods;

use App\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Goods
 * @package App\Modules\Goods
 *
 * @property number oper_id
 * @property number merchant_id
 * @property string name
 * @property string desc
 * @property number market_price
 * @property number price
 * @property Carbon start_date
 * @property Carbon end_date
 * @property string thumb_url
 * @property string pic
 * @property string pic_list
 * @property string buy_info
 * @property number status
 * @property string ext_attr
 * @property string tags
 * @property number sort
 * @property number sell_number
 */
class Goods extends BaseModel
{
    use SoftDeletes;
    //

    //商品上下架状态
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

}
