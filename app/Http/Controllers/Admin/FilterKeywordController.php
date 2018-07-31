<?php
/**
 * 过滤关键词
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\FilterKeyword\FilterKeywordService;
use App\Result;

class FilterKeywordController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {
        $pageSize = request('pageSize', 15);
        $data = FilterKeywordService::getList($pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add()
    {
        $this->validate(request(), [
            'keyword' => 'required',
            'category' => 'array',
        ]);
        $keyword = request('keyword', '');
        $category = request('category', []);
        $status = request('status', 1);

        $filterKeyword = FilterKeywordService::add($keyword, $category, $status);

        return Result::success($filterKeyword);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'keyword' => 'required',
            'category' => 'array',
        ]);
        $id = request('id');
        $keyword = request('keyword', '');
        $category = request('category', []);
        $status = request('status', 1);

        $filterKeyword = FilterKeywordService::edit($id, $keyword, $category, $status);

        return Result::success($filterKeyword);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');

        $filterKeyword = FilterKeywordService::changeStatus($id);

        return Result::success($filterKeyword);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function delete()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');

        $filterKeyword = FilterKeywordService::delete($id);

        return Result::success($filterKeyword);
    }
}