<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/20/020
 * Time: 15:38
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperStatisticsService;
use App\Result;

class StatisticsController extends Controller
{
    public function oper()
    {
        $startDate = request('startDate');
        $endDate = request('endDate');

        $data = OperStatisticsService::getList([
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}