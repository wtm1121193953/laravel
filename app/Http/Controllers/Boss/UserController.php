<?php
/**
 * User: Evan Lee
 * Date: 2017/8/18
 * Time: 15:07
 */

namespace App\Http\Controllers\Boss;


use App\Http\Controllers\Controller;
use App\Modules\BossAuth\BossUser;
use App\Result;

class UserController extends Controller
{

    public function getList()
    {
        $list = BossUser::paginate();
        return Result::success(['list' => $list->items(), 'total' => $list->total()]);
    }
}