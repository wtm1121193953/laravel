<?php

namespace App\Modules\User;

use App\BaseModel;

class UserStatistics extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
