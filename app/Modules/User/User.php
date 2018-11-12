<?php

namespace App\Modules\User;

use App\BaseModel;
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
 * @property string avatar_url
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

    const STATUS_NORMAL = 1;
    const STATUS_FORBIDDEN = 2;
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

    public function identityAuditRecord()
    {
        return $this->hasOne(UserIdentityAuditRecord::class);
    }

    public static function getStatusText($status)
    {
        $status_arr = [self::STATUS_NORMAL=>'正常',self::STATUS_FORBIDDEN=>'禁用'];
        return !empty($status_arr[$status])?$status_arr[$status]:'未知状态';
    }


}
