<?php
/**
 * 结算数据【旧】
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Result;
use Illuminate\Support\Facades\Log;
use App\Modules\Settlement\SettlementService;

class SettlementController extends Controller
{

    /**
     * 获取结算列表【旧】
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {
        $startTime = microtime(true);
        $data = SettlementService::getListForSaas([
        ]);
        $endTime = microtime(true);

        Log::info('耗时: ', ['start time' => $startTime, 'end time' => $endTime, '耗时: ' => $endTime - $startTime]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }



    public function detail()
    {

    }






    /**
     * 下载Excel
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcel()
    {
    }


}