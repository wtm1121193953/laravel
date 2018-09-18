<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Bizer\BizerService;
use App\Result;

class BizerController extends Controller
{
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
}