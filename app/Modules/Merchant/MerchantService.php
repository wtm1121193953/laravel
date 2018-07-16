<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/16
 * Time: 15:49
 */

namespace App\Modules\Merchant;


use App\BaseService;
use App\Modules\Oper\Oper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MerchantService extends BaseService
{

    public static function getAllNames(array $data)
    {

    }

    /**
     * 查询商户列表
     * @param array $data 查询条件 {
     *      id,
     *      operId,
     *      creatorOperId,
     *      name,
     *      signboardName,
     *      auditStatus,
     *      startCreatedAt,
     *      endCreatedAt,
     *  }
     * @param bool $getWithQuery
     * @return Merchant|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $data, bool $getWithQuery=false)
    {
        $id = $data['id'];
        $operId = $data['operId'];
        $creatorOperId = $data['creatorOperId'];
        $name = $data['name'];
        $signboardName = $data['signboardName'];
        $auditStatus = $data['auditStatus'];
        $startCreatedAt = $data['startCreatedAt'];
        $endCreatedAt = $data['endCreatedAt'];

        // 全局限制条件
        $query = Merchant::where('audit_oper_id', '>', 0)->orderByDesc('id');

        // 筛选条件
        if($id){
            $query->where('id', $id);
        }
        if($creatorOperId){
            if(is_array($creatorOperId) || $creatorOperId instanceof Collection){
                $query->whereIn('creator_oper_id', $creatorOperId);
            }else {
                $query->where('creator_oper_id', $creatorOperId);
            }
        }
        if($operId){
            if(is_array($operId) || $operId instanceof Collection){
                $query->where(function (Builder $query) use ($operId) {
                    $query->whereIn('oper_id',  $operId)
                        ->orWhereIn('audit_oper_id', $operId);
                });
            }else {
                $query->where(function ($query) use ($operId) {
                    $query->where('oper_id',  $operId)
                        ->orWhere('audit_oper_id', $operId);
                });
            }
        }
        if(!empty($auditStatus)){
            if(is_array($auditStatus) || $auditStatus instanceof Collection){
                $query->whereIn('audit_status', $auditStatus);
            }else {
                $query->where('audit_status', $auditStatus);
            }
        }
        if($startCreatedAt && $endCreatedAt){
            $query->whereBetween('created_at', [$startCreatedAt, $endCreatedAt]);
        }else if($startCreatedAt){
            $query->where('created_at', '>=', $startCreatedAt . ' 00:00:00');
        }else if($endCreatedAt){
            $query->where('created_at', '<=', $endCreatedAt . ' 23:59:59');
        }
        if($name){
            $query->where('name', 'like', "%$name%");
        }
        if($signboardName){
            $query->where('signboard_name', 'like', "%$signboardName%");
        }

        if($getWithQuery){
            return $query;
        }else {

            $data = $query->paginate();

            $data->each(function ($item) {
                $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
                $item->business_time = json_decode($item->business_time, 1);
                $item->operName = Oper::where('id', $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id)->value('name');
                $item->operId = $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id;
                $item->creatorOperId = $item->creator_oper_id;
                $item->creatorOperName = Oper::where('id', $item->creator_oper_id)->value('name');
            });

            return $data;
        }
    }

    public static function detail()
    {

    }

    public static function edit()
    {

    }

    public static function add()
    {

    }

    public static function addFromDraft()
    {

    }

    public static function addFromMerchantPool()
    {

    }
}