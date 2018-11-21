<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/21/021
 * Time: 15:30
 */
namespace App\Http\Controllers\Cs;

use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantCategoryService;
use App\Result;

class CategoryController extends Controller
{
    public function getList()
    {

        $params['cs_merchant_id'] = 1000000000;
        $params['cs_category_parent_id'] = 0 ;
        $data = CsMerchantCategoryService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function changeStatus()
    {

        $cs_merchant_id = 1000000000;
        $data = CsMerchantCategoryService::changeStatus(request('id'),$cs_merchant_id);

        return Result::success($data);
    }
}