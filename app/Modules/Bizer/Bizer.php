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

    public function bizerIdentityAuditRecord()
    {
        return $this->hasOne('App\Modules\Bizer\BizerIdentityAuditRecord');
    }
}
