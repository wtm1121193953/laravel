<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 12:02
 */
namespace App\Modules\Payment;

use App\BaseService;
use App\Exceptions\ParamInvalidException;
use test\Mockery\Fixtures\EmptyTestCaseV5;

class PaymentService extends BaseService
{

    public static function getList(array $params=[])
    {
        $query = Payment::query();

        $query->orderBy('id','desc');
        $data = $query->paginate();
        return $data;
    }


    public static function getPayment(int $type, int $terminal)
    {
        $terminals = [
            1=>'on_pc',
            2=>'on_app',
            3=>'on_miniprogram'
        ];
        if (empty($terminals[$terminal])) {
            throw new ParamInvalidException('参数错误');
        }

        $payment = Payment::where('status',1)
            ->where('type',$type)
            ->where($terminals[$terminal],'1')
            ->get();

        dd($payment);

    }
}