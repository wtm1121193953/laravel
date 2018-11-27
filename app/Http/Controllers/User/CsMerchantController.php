<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/21
 * Time: 10:57 AM
 */

namespace App\Http\Controllers\User;


use App\DataCacheService;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantService;
use App\Result;

class CsMerchantController extends Controller
{
    public function getList(){
        $user_key =  request()->get('current_device_no');
        if (empty($user_key)) {
            $user_key = request()->headers->get('token');
            if (empty($user_key)) {
                $user_key = request()->ip();
            }
        }

        $data = CsMerchantService::getListAndDistance([
            'city_id' => request('city_id'),
            'keyword' => request('keyword'),
            'lng' => request('lng'),
            'lat' => request('lat'),
            'radius' => request('radius'),
            'user_key' => $user_key,
            'onlyPayToPlatform' => 1,
            'pageSize' => request('pageSize',15),
        ]);

        return Result::success($data);

    }


    /**
     * 获取超市商户全部分类
     */
    public function getCategoryTree(){
        $this->validate(request(),[
            'merchant_id' => 'required|integer|min:1'
        ]);
        $cs_merchant_id = request('merchant_id');
        $list = DataCacheService::getCsMerchantCats($cs_merchant_id);

        if ($list) {
            $platform_useful = DataCacheService::getPlatformCatsUseful();
            foreach ($list as $k1=>$v1) {
                if ($v1['cat_id_level1'] == 0) {
                    continue;
                }
                if (empty($platform_useful[$v1['cat_id_level1']])) {
                    unset($list[$k1]);
                    continue;
                }
                if (!empty($v1['sub'])) {
                    foreach ($v1['sub'] as $k2=>$v2) {
                        if ($v2['cat_id_level2'] == 0) {
                            continue;
                        }
                        if (empty($platform_useful[$v2['cat_id_level2']])) {
                            unset($list[$k1]['sub'][$k2]);
                            continue;
                        }
                    }
                    sort($v1['sub']);
                }
            }
            sort($list);
        }

        return Result::success(['list'=>$list]);
    }


}