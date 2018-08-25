<?php

namespace App\Modules\Settlement;

use App\BaseModel;

/**
 * Class SettlementPayBatch
 * @package App\Modules\Settlement
 *
 * @property string batch_no
 * @property integer batch_count
 * @property number batch_amount
 * @property integer type
 * @property integer status
 */
class SettlementPayBatch extends BaseModel
{
    //
    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }
}
