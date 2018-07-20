<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/20
 * Time: 12:25
 */

namespace App\Modules\FilterKeyword;


use App\BaseService;
use App\Exceptions\BaseResponseException;

class FilterKeywordService extends BaseService
{
    /**
     * @param string $keyword
     * @param array $category
     * @param int $status
     * @return FilterKeyword
     */
    public static function add($keyword = '', $category = [], $status = 1)
    {
        $exit = FilterKeyword::where('keyword', $keyword)->first();
        if (!empty($exit)){
            throw new BaseResponseException('关键词已存在');
        }

        $categoryNumber = 0;
        foreach ($category as $item) {
            $categoryNumber = $categoryNumber | $item;
        }

        $filterKeyword = new FilterKeyword();
        $filterKeyword->keyword = $keyword;
        $filterKeyword->status = $status;
        $filterKeyword->category_number = $categoryNumber;
        $filterKeyword->save();

        return $filterKeyword;
    }

    /**
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($pageSize = 15)
    {
        $data = FilterKeyword::orderBy('created_at', 'desc')
            ->paginate($pageSize);

        $data->each(function ($item) {
            $category = [];
            for ($i = 0; $i <= 2; $i++) {
                $pair = pow(2, $i);
                if ($pair == ($item->category_number & $pair)){
                    array_push($category, $pair);
                }
            }
            $item->category = $category;
        });

        return $data;
    }

    /**
     * @param $id
     * @param string $keyword
     * @param array $category
     * @param int $status
     * @return FilterKeyword
     */
    public static function edit($id, $keyword = '', $category = [], $status = 1)
    {
        $exit = FilterKeyword::where('id', '<>', $id)->where('keyword', $keyword)->first();
        if (!empty($exit)){
            throw new BaseResponseException('关键词已存在');
        }

        $categoryNumber = 0;
        foreach ($category as $item) {
            $categoryNumber = $categoryNumber | $item;
        }

        $filterKeyword = FilterKeyword::findOrFail($id);
        $filterKeyword->keyword = $keyword;
        $filterKeyword->status = $status;
        $filterKeyword->category_number = $categoryNumber;
        $filterKeyword->save();

        return $filterKeyword;
    }

    /**
     * @param $id
     * @return FilterKeyword
     */
    public static function changeStatus($id)
    {
        $filterKeyword = FilterKeyword::findOrFail($id);
        $filterKeyword->status = $filterKeyword->status == 1 ? 2 : 1;
        $filterKeyword->save();

        return $filterKeyword;
    }

    /**
     * @param $id
     * @return FilterKeyword
     * @throws \Exception
     */
    public static function delete($id)
    {
        $filterKeyword = FilterKeyword::findOrFail($id);
        $filterKeyword->delete();

        return $filterKeyword;
    }
}