<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/14/014
 * Time: 15:13
 */
namespace App\Modules\Bank;

use App\BaseModel;

class Bank extends BaseModel
{

    const STATUS_NORMAL = 1; //启用
    const STATUS_FORBIDEN = 2; //禁用

    public static function getStatusVal($status)
    {
        $status_arr = [
            self::STATUS_NORMAL => '启用',
            self::STATUS_FORBIDEN => '禁用'
        ];
        return !empty($status_arr[$status])?$status_arr[$status]:'错误状态';
    }
}