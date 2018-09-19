<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\BaseResponseException;

class AllowWithdrawDate
{
    public $withdrawDay = [10,20,30];           // 指点提现日期
    /**
     * Handle an incoming request.
     * 中间件过滤提现日期
     * Author:  Jerry
     * Date:    190905
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $date = date('d');
        // 非指定日期不可提现
        /*if(!in_array($date, $this->withdrawDay))
        {
            throw new BaseResponseException('每月：' . implode('号、 ',$this->withdrawDay).' 号 才能提现');

        }*/
        return $next($request);
    }
}
