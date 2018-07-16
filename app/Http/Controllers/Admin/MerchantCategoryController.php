<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/18
 * Time: 22:49
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantCategoryService;
use App\Result;

class MerchantCategoryController extends Controller
{

    public function getTree()
    {
        $tree = MerchantCategoryService::getTree(true);
        return Result::success(['list' => $tree]);
    }

    public function getList()
    {
        $data = MerchantCategoryService::getList();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required'
        ]);
        $category = MerchantCategoryService::add(
            request('name'),
            request('status', 1),
            request('pid', 0)
        );

        return Result::success($category);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $category = MerchantCategoryService::edit(
            request('id'),
            request('name'),
            request('status', 1),
            request('pid', 0)
        );
        return Result::success($category);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer|in:1,2',
        ]);
        $category = MerchantCategoryService::changeStatus(request('id'), request('status', 1));
        return Result::success($category);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);

        $category = MerchantCategoryService::delete(request('id'));

        return Result::success($category);
    }
}