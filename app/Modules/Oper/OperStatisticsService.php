<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/20/020
 * Time: 15:43
 */

namespace App\Modules\Oper;

use App\BaseService;
use tests\Mockery\Adapter\Phpunit\EmptyTestCase;

class OperStatisticsService extends BaseService
{
    public static function getList(array $params = [],bool $return_query = false)
    {
        $query = OperStatistics::select('*');


        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $query->where('date', '>=', $params['startDate']);
            $query->where('date', '<=', $params['endDate']);
        }
        if (!empty($params['oper_id'])) {
            $query->where('oper_id', '=', $params['oper_id']);
        }

        $query->orderBy('id', 'desc');
        $query->with('oper:id,name');

        if ($return_query) {
            return  $query;
        }
        $data = $query->paginate();

        return $data;
    }
}