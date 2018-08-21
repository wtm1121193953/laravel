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
     * 查询业务员
     * @param array $data
     * @param bool $getWithQuery
     * @return type
     */
    public static function getList(array $data, bool $getWithQuery = false)
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
}