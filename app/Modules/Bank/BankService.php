<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/14/014
 * Time: 15:14
 */
namespace App\Modules\Bank;


use Illuminate\Database\Eloquent\Builder;

class BankService
{

    /**
     * @param array $params
     * @param bool  $return_query
     * @return Bank|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $params = [], bool $return_query = false)
    {
        $statusArr = [];
        if($params['status']){
            if(is_array($params['status'])){
                $statusArr = $params['status'];
            }else{
                $statusArr = explode(',',$params['status']);
            }
        }
        $query  = Bank::select('id','name','created_at','status')
            ->when($params['name'], function (Builder $query) use ($params){
                $query->where('name','like','%'.$params['name'].'%');
            })
            ->when($statusArr, function (Builder $query) use ($statusArr){
                $query->whereIn('status', $statusArr);
            })
            ->orderByDesc('id');
        if ($return_query) {
            return  $query;
        }
        $data = $query->paginate();

        $data->each(function ($item) {
            $item->status_val = Bank::getStatusVal($item->status);
        });
        return $data;
    }


    /**
     * 更新状态
     * @param $id
     * @param $status
     * @return Oper
     */
    public static function changeStatus($id, $status)
    {

        $bank = Bank::findOrFail($id);

        $bank->status = $status;

        $bank->save();
        return $bank;
    }
}