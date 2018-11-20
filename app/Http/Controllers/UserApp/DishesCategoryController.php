<?php

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesCategoryService;
use App\Result;

class DishesCategoryController extends Controller
{
    /**
     * 获取全部分类列表
     */
    public function getAllList()
    {
        $status = 1;

        $list = DishesCategoryService::getAllList(request('merchant_id'),$status);

        return Result::success([
            'list' => $list,
        ]);
    }

}