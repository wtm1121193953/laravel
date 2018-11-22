<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/22/022
 * Time: 9:45
 */
namespace App\Http\Controllers\Cs;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public $_oper_id; //运营中心ID
    public $_cs_merchant_id; //超市商户ID

    public function __construct()
    {


    }

    public function __init()
    {
        $this->_oper_id = request()->get('current_user')->oper_id;
        $this->_cs_merchant_id = request()->get('current_user')->merchant_id;
    }

}