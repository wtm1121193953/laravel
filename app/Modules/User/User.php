<?php

namespace App\Modules\User;

use App\BaseModel;
use App\Modules\Order\OrderService;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @package App\Modules\User
 *
 * @property int    status
 * @property int    level
 * @property string name
 * @property string mobile
 * @property string email
 * @property string account
 * @property string password
 * @property string salt
 * @property string wx_nick_name
 * @property string wx_avatar_url
 * @property int order_count
 *
 */
class User extends BaseModel
{
    use Notifiable, GenPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'salt',
    ];

    public static function getLevelText($level)
    {
        return ['','萌新', '粉丝', '铁杆', '骨灰'][$level];
    }

    // 获取用户订单数获取器
    public function getOrderCountAttribute($value)
    {
        // 如果用户的订单数为null, 则统计用户总的下单数量
        if(is_null($value)){
            $orderCount = OrderService::getOrderCountByUserId($this->id);
            $this->order_count = $orderCount;
            $this->save();
        }
        return $this->order_count;
    }
}
