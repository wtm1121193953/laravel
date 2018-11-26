<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 21:01
 */

namespace App\Observers;


use App\DataCacheService;
use App\Modules\Oper\Oper;
use Illuminate\Support\Facades\Log;

class OperObserver
{

    public function saved(Oper $row)
    {
        Log::info('触发观察者 oper saved');
        DataCacheService::delOperDetail([$row->id]);
    }
}