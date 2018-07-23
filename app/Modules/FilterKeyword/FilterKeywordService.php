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
use App\Exceptions\DataNotFoundException;

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
            $category = self::getFilterKeywordCategoriesByCategoryNumber($item->category_number);
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

        $filterKeyword = self::findOrFail($id);
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
        $filterKeyword = self::findOrFail($id);
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
        $filterKeyword = FilterKeyword::find($id);
        if(empty($filterKeyword)){
            throw new DataNotFoundException('关键字不存在或已删除');
        }
        $filterKeyword->delete();

        return $filterKeyword;
    }

    /**
     * 通过分类类型的值，来判断name中是否包含非法关键字
     * @param $name
     * @param $category
     */
    public static function filterKeywordByCategory($name, $category)
    {
        $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\（|\）|\_|\+|\{|\}|\:|\：|\<|\>|\?|\？|\[|\]|\,|\，|\.|\。|\、|\！|\·|\￥|\¥|\/|\;|\'|\`|\-|\=|\\\|\||\s+/";
        $name = preg_replace($regex,"",$name);
        $keywordArray = self::getFilterKeywordsByCategory($category);
        foreach ($keywordArray as $value) {
            $position = mb_strpos($name, $value);
            if ($position !== false) {
                throw new BaseResponseException('名称中包含非法关键词「'. $value .'」');
            }
        }
    }

    /**
     * 通过分类类型的总值，获取该关键词分类适用的数组
     * @param $categoryNumber
     * @return array
     */
    private static function getFilterKeywordCategoriesByCategoryNumber($categoryNumber)
    {
        $category = [];
        for ($i = 0; $i <= 2; $i++) {
            $pair = pow(2, $i);
            if ($pair == ($categoryNumber & $pair)){
                array_push($category, $pair);
            }
        }
        return $category;
    }

    /**
     * 通过分类类型的值，获取适用于该分类的关键词的数组
     * @param $category
     * @return array
     */
    private static function getFilterKeywordsByCategory($category)
    {
        $keywordArray = [];
        $data = FilterKeyword::where('status', FilterKeyword::STATUS_ON)->get();
        foreach ($data as $item) {
            if ($category == ($item->category_number & $category)) {
                array_push($keywordArray, $item->keyword);
            }
        }
        return $keywordArray;
    }

    private static function findOrFail($id)
    {
        $filterKeyword = FilterKeyword::find($id);
        if(empty($filterKeyword)){
            throw new DataNotFoundException('关键字不存在或已删除');
        }
        return $filterKeyword;
    }
}