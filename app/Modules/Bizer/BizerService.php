<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/16
 * Time: 15:49
 */

namespace App\Modules\Bizer;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Dishes\DishesGoods;
use App\Support\Lbs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BizerService extends BaseService
{

    

    /**
     * 查询所有业务员，不分页
     * @param array $data
     * @param bool $getWithQuery
     * @return type
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
    public static function getById($id, $fields = ['*'])
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        $bizer = Bizer::find($id, $fields);
        return $bizer;
    }
}