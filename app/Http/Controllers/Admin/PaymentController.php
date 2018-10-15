<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 12:27
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Payment;
use App\Modules\Payment\PaymentService;
use App\Result;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function getList()
    {
        $data = PaymentService::getList();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
        ]);

        $payment = new Payment();
        $payment->name = $request->get('name');
    }

}