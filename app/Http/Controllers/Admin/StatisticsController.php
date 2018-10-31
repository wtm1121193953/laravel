<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exports\StatisticsExport;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\MerchantStatisticsService;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperStatisticsService;
use App\Modules\User\UserService;
use App\Modules\User\UserStatisticsService;
use App\Result;
use Illuminate\Support\Carbon;

class StatisticsController extends Controller
{
    /**
     * 获取营销统计列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
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
            $operId = request('operId');
            $data = OperStatisticsService::getList([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'operId' => $operId,
                'page' => $page,
                'orderColumn' => $orderColumn,
                'orderType' => $orderType,
            ]);
        } elseif ($steType == 2) {
            $merchantId = request('merchantId');
            $data = MerchantStatisticsService::getList([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'merchantId' => $merchantId,
                'page' => $page,
                'orderColumn' => $orderColumn,
                'orderType' => $orderType,
            ]);
        } elseif ($steType == 1) {
            $userId = request('userId');
            $data = UserStatisticsService::getList([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'userId' => $userId,
                'page' => $page,
                'orderColumn' => $orderColumn,
                'orderType' => $orderType,
            ]);
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

    /**
     * 导出营销统计excel
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel()
    {
        $timeType = request('timeType');
        switch ($timeType) {
            case 'all':
                $startDate = null;
                $endDate = Carbon::now()->endOfDay();
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

        if($startDate && $startDate instanceof Carbon){
            $startDate = $startDate->format('Y-m-d');
        }
        if($endDate && $endDate instanceof Carbon){
            $endDate = $endDate->format('Y-m-d');
        }

        $steType = request('staType');
        if ($steType == 3) {
            $operId = request('operId');
            $params = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'operId' => $operId,
                'steType' => $steType,
            ];
            $data = OperStatisticsService::getList($params,true);
            return (new StatisticsExport($data, $params))->download('运营中心营销报表.xlsx');
        } elseif ($steType == 2) {
            $merchantId = request('merchantId');
            $params = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'merchantId' => $merchantId,
                'steType' => $steType,
            ];
            $data = MerchantStatisticsService::getList($params, true);
            return (new StatisticsExport($data, $params))->download('商户营销报表.xlsx');
        } elseif ($steType == 1) {
            $userId = request('userId');
            $params = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'userId' => $userId,
                'steType' => $steType,
            ];
            $data = UserStatisticsService::getList($params, true);
            return (new StatisticsExport($data, $params))->download('用户营销报表.xlsx');
        } else {
            throw new BaseResponseException('该营销统计类型不存在');
        }
    }

    /**
     * 获取所有商户
     * @return \Illuminate\Http\JsonResponse
     */
    public function allMerchants()
    {
        $merchants = MerchantService::getAllNames();

        return Result::success($merchants);
    }

    /**
     * 获取所有用户
     * @return \Illuminate\Http\JsonResponse
     */
    public function allUsers()
    {
        $users = UserService::getAll();

        return Result::success($users);
    }

    /**
     * 获取邀请列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInviteUserRecords()
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

        if($startDate && $startDate instanceof Carbon){
            $startDate = $startDate->format('Y-m-d');
        }
        if($endDate && $endDate instanceof Carbon){
            $endDate = $endDate->format('Y-m-d');
        }
        $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDate = date('Y-m-d 23:59:59', strtotime($endDate));

        $this->validate(request(), [
            'originId' => 'required',
            'originType' => 'required'
        ]);
        $originId = request('originId');
        $originType = request('originType');

        $params = compact('originType', 'originId', 'startDate', 'endDate');
        $data = InviteUserService::getInviteRecordList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}