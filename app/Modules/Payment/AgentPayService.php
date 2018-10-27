<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/27/027
 * Time: 14:41
 */
namespace App\Modules\Payment;

use App\BaseService;
use Illuminate\Database\Eloquent\Builder;

class AgentPayService extends BaseService
{
    public static function getList(array $params=[])
    {

        $query = AgentPay::query()
            ->when($params['name'],function (Builder $query) use($params) {
                $query->where('name','like',"%{$params['name']}%");
            })
        ;

        $query->orderBy('id','desc');
        $data = $query->paginate();
        return $data;
    }
}