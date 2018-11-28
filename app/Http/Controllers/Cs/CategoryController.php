<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/21/021
 * Time: 15:30
 */
namespace App\Http\Controllers\Cs;

use App\Modules\Cs\CsMerchantCategoryService;
use App\Result;

class CategoryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

    }

    public function getList()
    {
        parent::__init();
        CsMerchantCategoryService::synPlatFormCat($this->_cs_merchant_id);
        $params['cs_merchant_id'] = $this->_cs_merchant_id;
        $params['cs_category_parent_id'] = request('cs_category_parent_id',0) ;
        $data = CsMerchantCategoryService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function changeStatus()
    {

        parent::__init();
        $cs_merchant_id = $this->_cs_merchant_id;
        $data = CsMerchantCategoryService::changeStatus(request('id'),$cs_merchant_id);

        return Result::success($data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function changeSort()
    {
        parent::__init();
        $this->validate(request(), [
            'platform_category_id' => 'required|integer|min:1',
            'type' => 'required',
        ]);
        $type = request('type');
        $cs_merchant_id = $this->_cs_merchant_id;

        CsMerchantCategoryService::changeSort(request('platform_category_id'), $cs_merchant_id, $type);

        return Result::success();
    }
}