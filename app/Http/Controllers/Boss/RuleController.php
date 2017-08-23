<?php
/**
 * User: Evan Lee
 * Date: 2017/8/22
 * Time: 11:19
 */

namespace App\Http\Controllers\Boss;


use App\Http\Controllers\Controller;
use App\Modules\BossAuth\BossAuthRule;
use App\Result;

class RuleController extends Controller
{

    public function getList()
    {
        return Result::success(['list' => BossAuthRule::all()]);
    }
}