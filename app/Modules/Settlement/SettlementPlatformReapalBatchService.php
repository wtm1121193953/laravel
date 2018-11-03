<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/2/002
 * Time: 11:17
 */
namespace App\Modules\Settlement;

use App\BaseService;

class SettlementPlatformReapalBatchService extends BaseService
{

    public static function genBatchNo()
    {
        return 'dq'.date('YmdHis') . rand(10000,99999);
    }


}