<?php

namespace App\Modules\Settlement;

use App\BaseModel;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;

class SettlementPlatform extends BaseModel
{
    //
    //
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function oper()
    {
        return $this->belongsTo(Oper::class);
    }
}
