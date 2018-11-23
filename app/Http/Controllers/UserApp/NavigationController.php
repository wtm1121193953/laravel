<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/11/23
 * Time: 17:07
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Result;

class NavigationController extends Controller
{

    public function index()
    {
        // type: cs_index-超市首页, merchant_category-某个类目商户列表, merchant_category_all-商户类目列表, h5-h5链接
        $list = [
            [
                'title' => '大千超市',
                'icon' => '',
                'type' => 'cs_index',
            ],
            [
                'title' => '美食',
                'icon' => '',
                'type' => 'merchant_category',
                'category_id' => 1,
            ],
            [
                'title' => '更多',
                'icon' => '',
                'type' => 'merchant_category_all',
            ],
        ];
        return Result::success($list);
    }
}