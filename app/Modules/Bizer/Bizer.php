<?php

namespace App\Modules\Bizer;

use App\BaseModel;
use App\Modules\User\GenPassword;

/**
 * Class Bizer
 * @package App\Modules\Bizer
 * @property string name
 * @property string mobile
 * @property string password
 * @property string salt
 * @property integer status
 */

class Bizer extends BaseModel {

    use GenPassword;

    /**
     * 状态 1-正常  2-禁用
     */
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

    public function bizerIdentityAuditRecord()
    {
        return $this->hasOne('App\Modules\Bizer\BizerIdentityAuditRecord');
    }

    public function Oper()
    {
        
    }
}
