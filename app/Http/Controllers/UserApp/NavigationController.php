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
                'icon' => 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/8c7c3424eee4adef37033a6320bc4b76.png',
                'type' => 'merchant_category',
                'payload' => [
                    'category_id' => 1
                ],
            ],
            [
                'title' => '周边游',
                'icon' => 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/87b5e58ac2dc0925fd7336d75ab5f185.png',
                'type' => 'merchant_category',
                'payload' => [
                    'category_id' => 29
                ],
            ],
            [
                'title' => '休闲娱乐',
                'icon' => 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/ff2ee3d6bd48cf96a0b608234cda06d5.png',
                'type' => 'merchant_category',
                'payload' => [
                    'category_id' => 46
                ],
            ],
            [
                'title' => '更多',
                'icon' => '',
                'type' => 'merchant_category_all',
            ],
        ];
        return Result::success(['list' => $list]);
    }
}