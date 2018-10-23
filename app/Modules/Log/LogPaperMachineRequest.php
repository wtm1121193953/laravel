<?php

namespace App\Modules\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LogPaperMachineRequest
 * @package App\Modules\Log
 * @property string mobile
 * @property string request
 * @property string response
 * @property tinyint type
 */
class LogPaperMachineRequest extends Model
{
    //
    const TYPE_GET = 1;
    const TYPE_POST= 2;
}
