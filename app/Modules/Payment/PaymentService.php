<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 12:02
 */
namespace App\Modules\Payment;

use App\BaseService;

class PaymentService extends BaseService
{

    public static function getList(array $params=[])
    {
        $query = Payment::query();

        $query->orderBy('id','desc');
        $data = $query->paginate();
        return $data;
    }
}