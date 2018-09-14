<?php

namespace App\Modules\Bizer;

use App\BaseService;

class BizerService extends BaseService
{


    /**
     * 查询所有业务员，不分页
     * @param array $data
     * @param bool $getWithQuery
     * @return Bizer|Bizer[]|array|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAll(array $data, bool $getWithQuery = false)
    {
        $status = array_get($data,"status");
        // 全局限制条件
        $query = Bizer::where('status', $status)->orderByDesc('id');

        if ($getWithQuery) {
            return $query;
        } else {

            $data = $query->get();

            return $data;
        }
    }

    /**
     * 通过id获取业务员
     * @param $id
     * @param array $fields
     * @return Bizer
     */
    public static function getById($id, $fields = ['*'])
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        $bizer = Bizer::find($id, $fields);
        return $bizer;
    }
}