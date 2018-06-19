<?php

namespace App\Modules\User;

use App\BaseModel;
use Illuminate\Notifications\Notifiable;

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
}
