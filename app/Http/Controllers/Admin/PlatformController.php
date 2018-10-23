<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/19/019
 * Time: 16:32
 */
namespace App\Http\Controllers\Admin;

use App\Exports\PlatformTradeRecordsDailyExport;
use App\Http\Controllers\Controller;
use App\Modules\Platform\PlatformTradeRecordsDailyService;
use App\Result;

class PlatformController extends Controller
{
    public function getDailyList()
    {

        $params = [
            'startTime' => request('startTime'),
            'endTime' => request('endTime'),
        ];

        $data = PlatformTradeRecordsDailyService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }


    public function dailyListExport()
    {

        $params = [
            'startTime' => request('startTime'),
            'endTime' => request('endTime'),
        ];

        if ($params['startTime'] == 'null') {
            $params['startTime'] = '';
        }
        if ($params['endTime'] == 'null') {
            $params['endTime'] = '';
        }

        $data = PlatformTradeRecordsDailyService::getList($params,true);

        return (new PlatformTradeRecordsDailyExport($data, $params))->download('交易汇总表.xlsx');

    }
}