<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Area\Area;
use App\Modules\Goods\GoodsService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategory;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Merchant\MerchantFollow;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\MerchantSettingService;
use App\Modules\Setting\SettingService;
use App\Result;
use App\Support\Lbs;
use App\Support\Utils;
use Illuminate\Database\Eloquent\Builder;

class MerchantController extends Controller
{

    public function getList()
    {
        $noPilot = false;
        if (isset($_SERVER['HTTP_X_VERSION'])) {
            $miniprogramVersion = $_SERVER['HTTP_X_VERSION'];
            $noPilot = $miniprogramVersion < 'v1.4.0';
        }

        $merchantShareInMiniprogram = SettingService::getValueByKey('merchant_share_in_miniprogram');
        $currentOperId = request()->get('current_oper_id');
        $current_open_id = $merchantShareInMiniprogram != 1?$currentOperId:0;

        $data = MerchantService::getListAndDistance([
            'city_id' => request('city_id'),
            'merchant_category_id' => request('merchant_category_id'),
            'keyword' => request('keyword'),
            'lng' => request('lng'),
            'lat' => request('lat'),
            'noPilot' => $noPilot,
            'current_open_id' => $current_open_id,
            'radius' => request('radius'),
            'lowest_price' => request('lowest_price'),
            'highest_price' => request('highest_price'),
            'user_key' => request()->get('current_open_id'),
            'onlyPayToPlatform' => 1,
        ]);

        if (!empty($data['list'])) {
            $data['list']->each(function ($item) use ($currentOperId) {
                // 判断商户是否是当前小程序关联运营中心下的商户
                $item->isOperSelf = $item->oper_id === $currentOperId ? 1 : 0;
            });
        }
        return Result::success($data);
    }

    /**
     * 获取商户详情
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $lng = request('lng');
        $lat = request('lat');

        $userId = request()->get('current_user')->id ?? 0;

        $detail = Merchant::findOrFail($id);
        $detail->desc_pic_list = $detail->desc_pic_list ? explode(',', $detail->desc_pic_list) : [];
        if($detail->business_time) $detail->business_time = json_decode($detail->business_time, 1);
        if($lng && $lat){
            $distance = Lbs::getDistanceOfMerchant($id, request()->get('current_open_id'), floatval($lng), floatval($lat));
            // 格式化距离
            $detail->distance = Utils::getFormativeDistance($distance);
        }
        $category = MerchantCategory::find($detail->merchant_category_id);


        //商家是否被当前用户关注
        $isFollows = MerchantFollow::where('merchant_id',$id)->where('user_id',$userId)->where('status',MerchantFollow::USER_YES_FOLLOW)->first();
        $detail->isFollows = empty($isFollows)? 1 : 2;

        $detail->merchantCategoryName = $category->name;
        //商家是否开启单品模式
        $detail->isOpenDish = MerchantSettingService::getValueByKey($id,'dishes_enabled');
        // 商家是否开启直接买单
        $detail->isOpenQrcodePay = 1;
        // 最低消费
        $detail->lowestAmount = MerchantService::getLowestPriceForMerchant($detail->id);
        $currentOperId = request()->get('current_oper_id');
        // 判断商户是否是当前小程序关联运营中心下的商户
        $detail->isOperSelf = $detail->oper_id === $currentOperId ? 1 : 0;
        // 兼容v1.0.0版客服电话字段
        $detail->contacter_phone = $detail->service_phone;
        // 商户评级字段，暂时全部默认为5星
        $detail->grade = 5;

        return Result::success(['list' => $detail]);
    }
}