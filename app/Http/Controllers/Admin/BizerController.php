<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Bizer\BizerService;
use App\Result;

class BizerController extends Controller
{
    /**
     * 获取业务员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $mobile = request('mobile', '');
        $id = request('id', 0);
        $name = request('name', '');
        $bizerStartDate = request('startDate', '');
        $bizerEndDate = request('endDate', '');
        $status = request('status', 0);
        $identityStatus = request('identityStatus', 0);
        $identityStartDate = request('identityStartDate', '');
        $identityEndDate = request('identityEndDate', '');
        $pageSize = request('pageSize', 15);

        $params = compact('mobile', 'id', 'name', 'bizerEndDate', 'bizerStartDate', 'status', 'identityStatus', 'identityStartDate', 'identityEndDate');
        $data = BizerService::getBizerList($params, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取业务员详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $bizerId = request('id');
        $bizer = BizerService::getBizerDetail($bizerId);

        return Result::success($bizer);
    }

    /**
     * 更改业务员的状态
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $bizerId = request('id');
        $bizer = BizerService::changeStatus($bizerId);

        return Result::success($bizer);
    }
}