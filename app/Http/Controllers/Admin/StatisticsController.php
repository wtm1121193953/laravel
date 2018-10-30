<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/20/020
 * Time: 15:38
 */
namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exports\StatisticsOperExport;
use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperStatisticsService;
use App\Result;
use Illuminate\Support\Carbon;

class StatisticsController extends Controller
{
    public function oper()
    {
        $timeType = request('timeType');
        $page = request('page');
        if (empty($page)) {
            $page = 1;
        }
        switch ($timeType) {
            case 'all':
                $startDate = null;
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'today'://今天的数据需要实时统计，只有第一页做统计，不然翻页慢
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::now()->subDay()->startOfDay();
                $endDate = $startDate->copy()->endOfDay();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'lastMonth':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
                break;
            default:
                $startDate = request('startDate');
                $endDate = request('endDate');
                break;
        }

        $oper_id = request('oper_id');
        if($startDate && $startDate instanceof Carbon){
            $startDate = $startDate->format('Y-m-d');
        }
        if($endDate && $endDate instanceof Carbon){
            $endDate = $endDate->format('Y-m-d');
        }

        $orderColumn = request('orderColumn', null);
        $orderType = request('orderType', null);
        $steType = request('staType');

        if ($steType == 3) {
            $data = OperStatisticsService::getList([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'oper_id' => $oper_id,
                'page' => $page,
                'orderColumn' => $orderColumn,
                'orderType' => $orderType,
            ]);
        } elseif ($steType == 2) {

        } elseif ($steType == 1) {

        } else {
            throw new BaseResponseException('该营销统计类型不存在');
        }

        return Result::success([
            'list' => $data['data'],
            'total' => $data['total'],
        ]);
    }

    public function allOpers()
    {

        $data = OperService::allOpers();
        return Result::success([
            'list' => $data,
        ]);

    }

    public function operExport()
    {
        $timeType = request('timeType');
        switch ($timeType) {
            case 'all':
                $startDate = null;
                $endDate = null;
                break;
            case 'today':
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::now()->subDay()->startOfDay();
                $endDate = $startDate->copy()->endOfDay();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'lastMonth':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
                break;
            default:
                $startDate = request('startDate');
                $endDate = request('endDate');
                break;
        }

        $oper_id = request('oper_id');
        if($startDate && $startDate instanceof Carbon){
            $startDate = $startDate->format('Y-m-d');
        }
        if($endDate && $endDate instanceof Carbon){
            $endDate = $endDate->format('Y-m-d');
        }

        $params = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'oper_id' => $oper_id,
        ];
        $data = OperStatisticsService::getList($params,true);
        return (new StatisticsOperExport($data, $params))->download(' 运营中心营销报表.xlsx');
    }
}