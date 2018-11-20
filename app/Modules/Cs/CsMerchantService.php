<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use App\BaseService;

class CsMerchantService extends BaseService {

    public static function getById($id, $fields = ['*'])
    {
        return CsMerchant::find($id, $fields);
    }

    /**
     * 获取商家列表可以计算距离
     * @param $params
     */
    public static function getListAndDistance(array $params = []){



        $list = [
            ['id'=>1,
                'name'=>'商户名1',
                'signboard_name'=>'商户招牌1',
                'order_number_30d'=>50,
                'grade'=>5,
            ],
            ['id'=>2,
                'name'=>'商户名2',
                'signboard_name'=>'商户招牌2',
                'order_number_30d'=>50,
                'grade'=>5,
            ]
        ];

        return ['list' => $list, 'total' => 2];

    }
}