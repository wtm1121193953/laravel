<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/21
 * Time: 10:57 AM
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantService;
use App\Result;

class CsMerchantController extends Controller
{
  public function getList(){
      $list = CsMerchantService::getListAndDistance();
      return Result::success([
          'list'=>$list
      ]);
  }

}