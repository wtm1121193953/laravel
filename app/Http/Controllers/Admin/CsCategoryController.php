<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/11/23
 * Time: 15:24
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsPlatformCategoryService;
use App\Result;
use App\Support\Utils;

class CsCategoryController extends Controller
{

    public function getAll()
    {
        $tree = CsPlatformCategoryService::getTree(false);
        $list = [];
        foreach ($tree as $item) {
            $list[] = $item;
            if(count($item['sub']) > 0){
                array_push($list, ...$item['sub']);
            }
        }
        return Result::success([
            'list' => $list,
            'tree' => $tree,
            'total' => count($list),
        ]);
    }

    public function getTree()
    {
        $tree = CsPlatformCategoryService::getTree(false);
        return Result::success(['tree' => $tree]);
    }

    /**
     * 添加分类信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function add()
    {
        $this->validate(request(), [
            'cat_name' => 'required'
        ]);
        $data = [
            'cat_name' => request('cat_name'),
            'status' => request('status', 1),
            'parent_id' => request('parent_id', 0),
        ];
        $cate = CsPlatformCategoryService::add($data);
        return Result::success($cate);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'cat_name' => 'required',
        ]);
        $data = [
            'cat_name' => request('cat_name'),
            'status' => request('status', 1),
            'parent_id' => request('parent_id', 0),
        ];
        $cate = CsPlatformCategoryService::edit(request('id'), $data);
        return Result::success($cate);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer|in:1,2',
        ]);
        $data = [
            'status' => request('status'),
        ];
        $cate = CsPlatformCategoryService::edit(request('id'), $data);
        return Result::success($cate);
    }


}