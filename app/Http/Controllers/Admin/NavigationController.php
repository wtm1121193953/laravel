<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/11/23
 * Time: 19:49
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Navigation\NavigationService;
use App\Result;

class NavigationController extends Controller
{

    public function getAllTopCategories()
    {
        $list = MerchantCategoryService::getAllTopCategories();
        return Result::success(['list' => $list]);
    }

    public function getAll()
    {
        $list = NavigationService::getAll();
        return Result::success([
            'list' => $list,
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'title' => 'required',
            'icon' => 'required',
            'type' => 'required',
        ]);
        $title = request('title');
        $icon = request('icon');
        $type = request('type');
        $categoryId = request('categoryId');
        if($type == 'merchant_category'){
            if(empty($categoryId)){
                throw new ParamInvalidException('分类ID不能为空');
            }
            $payload = [
                'category_id' => $categoryId,
            ];
        }else {
            $payload = [];
        }
        $nav = NavigationService::add([
            'title' => $title,
            'icon' => $icon,
            'type' => $type,
            'payload' => $payload,
        ]);
        return Result::success($nav);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'title' => 'required',
            'icon' => 'required',
            'type' => 'required',
        ]);
        $id = request('id');
        $title = request('title');
        $icon = request('icon');
        $type = request('type');
        $categoryId = request('categoryId');
        if($type == 'merchant_category'){
            if(empty($categoryId)){
                throw new ParamInvalidException('分类ID不能为空');
            }
            $payload = [
                'category_id' => $categoryId,
            ];
        }else {
            $payload = [];
        }

        $nav = NavigationService::edit($id, [
            'title' => $title,
            'icon' => $icon,
            'type' => $type,
            'payload' => $payload,
        ]);
        return Result::success($nav);
    }

    public function changeSort()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'sort' => 'required|integer',
        ]);
        $id = request('id');
        $sort = request('sort');

        $nav = NavigationService::changeSort($id, $sort);
        return Result::success($nav);
    }
}