<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\DataCacheService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Area\Area;
use App\Modules\Country\CountryService;
use App\Modules\CsStatistics\CsStatisticsMerchantOrder;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantFollow;
use App\Modules\Oper\Oper;
use App\Modules\Setting\SettingService;
use App\Support\Lbs;
use App\Support\Utils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CsMerchantService extends BaseService {


    /**
     * 通过名字获取ID数组
     * @param string $name
     * @return bool|\Illuminate\Support\Collection
     */
    public static function getIdsByName(string $name)
    {
        if (empty($name)) {
            return false;
        }
        return CsMerchant::where('name','like',"%{$name}%")->pluck('id');
    }
    /**
     * 根据ID获取商户信息
     * @param $id
     * @param array|string $fields
     * @return CsMerchant
     */
    public static function getById($id, $fields = ['*'])
    {
        if(is_string($fields)){
            $fields = explode(',', $fields);
        }
        return CsMerchant::find($id, $fields);
    }

    /**
     * 根据ID获取商户名称
     * @param $merchantId
     * @param null $default
     * @return null|string
     */
    public static function getNameById($merchantId, $default = null)
    {
        $merchant = self::getById($merchantId, ['name']);
        if (empty($merchant)) {
            return $default;
        }
        return $merchant->name;
    }

    /**
     * 获取商户的招牌名
     * @param $merchantId
     * @return mixed
     */
    public static function getSignboardNameById($merchantId)
    {
        $value = CsMerchant::where('id', $merchantId)->value('signboard_name');
        return $value;
    }

    /**
     * @param array $query
     * @return CsMerchant[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllNames(array $query = [])
    {
        // todo 后期可以对查询结果做缓存
        $auditStatus = array_get($query, 'audit_status');
        $status =array_get($query, 'status');
        $operId =array_get($query, 'operId'); //运营中心ID

        $query = CsMerchant::select('id', 'name')
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!empty($auditStatus), function (Builder $query) use ($auditStatus) {
                if ($auditStatus == -1) {
                    $auditStatus = 0;
                }
                $query->where('audit_status', $auditStatus);
            });

        if ($operId) {
            $query->where('oper_id', $operId);
        }

        /*if($isPilot){
            $data->where('is_pilot', CsMerchant::PILOT_MERCHANT);
        }else{
            $data->where('is_pilot', CsMerchant::NORMAL_MERCHANT);
        }*/

        $list = $query->orderBy('updated_at', 'desc')->get();

        return $list;
    }

    /**
     * 查询商户列表
     * @param array $data 查询条件 {
     *      id,
     *      operId,
     *      name,
     *      signboardName,
     *      auditStatus,
     *      startCreatedAt,
     *      endCreatedAt,
     *  }
     * @param bool $getWithQuery
     * @return CsMerchant|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $data, bool $getWithQuery = false)
    {
        $id = array_get($data,'id');
        $operId = array_get($data,'operId');
        $name = array_get($data,'name');
        $merchantId = array_get($data,'merchantId');
        $signboardName = array_get($data,'signboardName');
        $status = array_get($data,'status');
        $settlementCycleType = array_get($data,'settlementCycleType');
        $auditStatus = array_get($data,'auditStatus');
        $startCreatedAt = array_get($data,'startCreatedAt');
        $endCreatedAt = array_get($data,'endCreatedAt');
        $cityId = array_get($data, "cityId");

        // 全局限制条件
        $query = CsMerchant::orderByDesc('id');

        // 筛选条件
        if ($id) {
            $query->where('id', $id);
        }
        if ($operId) {
            if (is_array($operId) || $operId instanceof Collection) {
                $query->whereIn('oper_id', $operId);
            } else {
                $query->where('oper_id', $operId);
            }
        }

        if(!empty($cityId)){
            if(isset($cityId[0]) && $cityId[0]){
                $province_Id = Area::findOrFail($cityId[0]);
                $query->where('province_id', $province_Id->area_id);
            }
            if(isset($cityId[1]) && $cityId[1]){
                $city_Id = Area::findOrFail($cityId[1]);
                $query->where('city_id', $city_Id->area_id);
            }
            if(isset($cityId[2]) && $cityId[2]){
                $area_Id = Area::findOrFail($cityId[2]);
                $query->where('area_id', $area_Id->area_id);
            }
        }
        if (!empty($status)){
            // todo 不能根据status改变 audit_status
            if($status == 1){
                $query->where('status',1)->whereIn('audit_status',[1,3]);
            }elseif($status ==2){
                $query->where('status',2)->whereIn('audit_status',[1,3]);
            }else{
                $query->whereIn('audit_status',[0,2]);
            }
        }
        if ($settlementCycleType) {
            $query->whereIn('settlement_cycle_type', $settlementCycleType);
        }

        if ((!empty($auditStatus)||$auditStatus==0)&&$auditStatus!=null) {
            if (is_array($auditStatus) || $auditStatus instanceof Collection) {
                $query->whereIn('audit_status', $auditStatus);
            } else {
                if ($auditStatus == -1) $auditStatus = 0;
                $query->where('audit_status', $auditStatus);
            }
        }

        if ($startCreatedAt && $endCreatedAt) {
            if ($startCreatedAt == $endCreatedAt) {
                $query->whereDate('created_at', $startCreatedAt);
            } else {
                $query->whereBetween('created_at', [$startCreatedAt . ' 00:00:00', $endCreatedAt . ' 23:59:59']);
            }
        } else if ($startCreatedAt) {
            $query->where('created_at', '>=', $startCreatedAt . ' 00:00:00');
        } else if ($endCreatedAt) {
            $query->where('created_at', '<=', $endCreatedAt . ' 23:59:59');
        }
        if ($name) {
            $query->where('name', 'like', "%$name%");
        }
        if(!empty($merchantId)){
            if (is_array($merchantId) || $merchantId instanceof Collection) {
                $query->whereIn('id', $merchantId);
            } else {
                $query->where('id', $merchantId);
            }
        }
        if ($signboardName) {
            $query->where('signboard_name', 'like', "%$signboardName%");
        }

        if ($getWithQuery) {
            return $query;
        } else {

            $data = $query->paginate();
            $data->each(function ($item) {

                $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
                $item->account = MerchantAccount::where('merchant_id', $item->id)->where('type',MerchantAccount::TYPE_CS)->first();
                $item->business_time = json_decode($item->business_time, 1);
                $item->operName = Oper::where('id', $item->oper_id )->value('name');
            });
            return $data;
        }
    }

    /**
     * 根据ID获取商户详情
     * @param $id
     * @param $userId
     * @return CsMerchant
     */
    public static function detail($id, $userId)
    {

        $merchant = CsMerchant::findOrFail($id);
        $merchantAudit = CsMerchantAudit::where('cs_merchant_id',$id)->orderBy('id','desc')->first();
        $merchant->cs_merchant_id = $merchant->id;
        $merchant->account = $merchant->name;
        $merchant->audit_status = $merchantAudit->status;
        $merchant->changeData = json_decode($merchantAudit->data_modify,true);
        $merchant->merchant_status = $merchant->status;
        $merchant = self::makeDetail($merchant);
        $oper = Oper::where('id', $merchant->oper_id)->first();
        if ($oper) {
            $merchant->operAddress = $oper->province . $oper->city . $oper->area . $oper->address;
            $merchant->isPayToPlatform = in_array($oper->pay_to_platform, [Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING]);
            $merchant->operName = $oper->name;
        }
        return $merchant;
    }

    /**
     * @param $id
     * @param $userId
     * @return mixed
     */
    public static function getAuditDetail($id,$userId)
    {
        $merchantAudit = CsMerchantAudit::findOrFail($id);
        $merchant = json_decode($merchantAudit->data_after,false);
        $merchant->account = $merchant->name;
        $merchant = self::makeDetail($merchant);
        $merchant->id = $merchantAudit->id;
        $merchant->audit_status = $merchantAudit->status;
        $merchant->audit_suggestion = $merchantAudit->suggestion;
        $operId = isset($merchantAudit->oper_id) ? $merchantAudit->oper_id:0;
        $merchant->operName = Oper::where('id', $operId)->value('name');
        $merchant->cs_merchant_id = $merchantAudit->cs_merchant_id;
        $merchant->audit = $merchantAudit;
        $merchant->changeData = json_decode($merchantAudit->data_modify,true);
        if($merchantAudit->cs_merchant_id!=0){
            $csMerchant = CsMerchantService::getById($merchantAudit->cs_merchant_id);
            $merchant->merchant_status = $csMerchant->status;
        }

        $oper = Oper::where('id', $operId)->first();
        if ($oper) {
            $merchant->operAddress = $oper->province . $oper->city . $oper->area . $oper->address;
            $merchant->isPayToPlatform = in_array($oper->pay_to_platform, [Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING]);
        }
        return $merchant;
    }

    /**
     * 用以处理数据
     * @param $csMerchant
     * @return mixed
     */
    public static function makeDetail($csMerchant){
        $csMerchant->business_time = json_decode($csMerchant->business_time, 1);
        $csMerchant->desc_pic_list = $csMerchant->desc_pic_list ? explode(',', $csMerchant->desc_pic_list) : '';
        $csMerchant->contract_pic_url = $csMerchant->contract_pic_url ? explode(',', $csMerchant->contract_pic_url) : '';
        $csMerchant->other_card_pic_urls = $csMerchant->other_card_pic_urls ? explode(',', $csMerchant->other_card_pic_urls) : '';
        $csMerchant->bank_card_pic_a = $csMerchant->bank_card_pic_a ? explode(',', $csMerchant->bank_card_pic_a) : '';
        $csMerchant->countryName = CountryService::getNameZhById($csMerchant->country_id);
        return $csMerchant;
    }

    /**
     * 通过审核id获取最新修改的数据
     * @param $id
     * @return mixed
     */
    public static function getReEditData($id){

        $userId = request()->get('current_user')->id;
        $csMerchantAudit = CsMerchantAuditService::getById($id);
        $data = $csMerchantAudit->data_after;
        $merchant = json_decode($data,false);
        $merchant->account = $merchant->name;
        $merchant->business_time = json_decode($merchant->business_time, 1);
        $merchant->desc_pic_list = $merchant->desc_pic_list ? explode(',', $merchant->desc_pic_list) : '';
        $merchant->contract_pic_url = $merchant->contract_pic_url ? explode(',', $merchant->contract_pic_url) : '';
        $merchant->other_card_pic_urls = $merchant->other_card_pic_urls ? explode(',', $merchant->other_card_pic_urls) : '';
        $merchant->bank_card_pic_a = $merchant->bank_card_pic_a ? explode(',', $merchant->bank_card_pic_a) : '';

        $oper = Oper::where('id', $csMerchantAudit->oper_id)->first();
        if ($oper) {
            $merchant->operAddress = $oper->province . $oper->city . $oper->area . $oper->address;
            $merchant->isPayToPlatform = in_array($oper->pay_to_platform, [Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING]);
            $merchant->operName = $oper->name;
        }
        $merchantFollow = MerchantFollow::where('user_id',$userId)->where('merchant_id',$id);
        if($merchantFollow){
            $merchant->user_follow_status = 2;
        }else{
            $merchant->user_follow_status =1;
        }
        $merchant->countryName = CountryService::getNameZhById($merchant->country_id);
        return $merchant;
    }


    /**
     * SaaS编辑商户
     * @param $id
     * @return CsMerchant
     */
    public static function edit($id)
    {
        $csMerchant = CsMerchant::where('id', $id)->firstOrFail();
        $csMerchant->fillMerchantPoolInfoFromRequest();
        $csMerchant->fillMerchantActiveInfoFromRequest();

        if($csMerchant->bank_card_type == CsMerchant::BANK_CARD_TYPE_COMPANY){
            if($csMerchant->name != $csMerchant->bank_open_name){
                throw new ParamInvalidException('提交失败，申请T+1结算，商户名称需和开户名一致');
            }
        }elseif($csMerchant->bank_card_type == CsMerchant::BANK_CARD_TYPE_PEOPLE){
            if($csMerchant->corporation_name != $csMerchant->bank_open_name){
                throw new ParamInvalidException('提交失败，申请T+1结算，营业执照及法人姓名需和开户名一致');
            }
        }
        // 商户名不能重复
        //查询超市表
        $exists = CsMerchant::where('id','<>',$id)->where('name',$csMerchant->name)->first();
        //查询普通商户表
        $existsMerchant = Merchant::where('name', $csMerchant->name)->first();

        if(($exists&&$exists->id!=$id)||$existsMerchant){
            throw new ParamInvalidException('商户名称不能重复');
        }

        // 招牌名不能重复
        $signboardName = CsMerchant::where('id','<>',$id)->where('signboard_name', $csMerchant->signboard_name)->first();
        $existsMerchantSignboardName = Merchant::where('signboard_name',$csMerchant->signboard_name)->first();
        if ($signboardName || $existsMerchantSignboardName) {
            throw new ParamInvalidException('招牌名称不能重复');
        }

        $csMerchant->save();
        return $csMerchant;
    }



    /**
     * 同步商户地区数据到Redis中
     * @param $merchant Collection|Merchant
     */
    public static function geoAddToRedis($merchant)
    {
        if ($merchant instanceof Collection) {
            Lbs::csMerchantGpsAdd($merchant);
        } else {
            Lbs::csMerchantGpsAdd($merchant->id, $merchant->lng, $merchant->lat);
        }
    }

    /**
     * 同步所有商户的LBS数据到Redis中
     */
    public static function geoAddAllToRedis()
    {
        CsMerchant::chunk(100, function (Collection $list) {
            self::geoAddToRedis($list);
        });
    }


    /**
     * 更新商户的最低价格
     * @param $merchant_id
     */
    public static function updateMerchantLowestAmount($merchant_id)
    {
        $merchant = CsMerchant::findOrFail($merchant_id);
        $merchant->lowest_amount = self::getLowestPriceForMerchant($merchant_id);
        $merchant->save();
    }

    /**
     * 根据商家获取最低价商品的价格, 没有商品是返回null
     * @param $merchantId
     * @return number|null
     */
    public static function getLowestPriceForMerchant($merchantId)
    {
        $goodsLowestAmount = CsGood::where('cs_merchant_id', $merchantId)
                ->where('status', CsGood::STATUS_ON)
                ->orderBy('price')
                ->value('price') ?? 0;

        return $goodsLowestAmount;
    }

    /**
     * 获取商家列表可以计算距离
     * @param array $params
     * @return array
     */
    public static function getListAndDistance(array $params = [])
    {
        $city_id = array_get($params, 'city_id');
        $lng = array_get($params,'lng');
        $lat = array_get($params, 'lat');
        $keyword = array_get($params, 'keyword',null);
        $pageSize = array_get($params, 'pageSize',15);

        // 暂时去掉商户列表中的距离限制
        $radius = array_get($params, 'radius');
        $radius = $radius == 200000 ? 0 : $radius;
        // 价格搜索
        $lowestPrice = array_get($params,'lowest_price', 0);
        $highestPrice = array_get($params, 'highest_price', 0);
        if ($lowestPrice && $highestPrice && $lowestPrice > $highestPrice){
            throw new BaseResponseException('搜索条件的最低价格不能高于最高价格');
        }
        $onlyPayToPlatform = array_get($params, 'onlyPayToPlatform', 0);

        $distances = null;
        if($lng && $lat && $radius){
            // 如果经纬度及范围都存在, 则按距离筛选出附近的商家
            $distances = Lbs::getNearlyCsMerchantDistanceByGps($lng, $lat, $radius);
        }
        $user_key = array_get($params, 'user_key', 0); //终端的唯一表示，用于计算距离

        //只能查询切换到平台的商户
        $query = CsMerchant::where('status', CsMerchant::STATUS_ON)
            ->where('oper_id', '>', 0)
            ->whereIn('audit_status', [CsMerchant::AUDIT_STATUS_SUCCESS])
            ->when($city_id, function(Builder $query) use ($city_id){
                // 特殊城市，如澳门。属于省份，要显示下属所有城市的商户
                $areaInfo = Area::where('area_id', $city_id)->where('path', 1)->first();
                if (empty($areaInfo)) {
                    $query->where(function(Builder $query)use ($city_id){
                        $query->where('city_id', $city_id)
                            ->orWhere('area_id', $city_id);
                    });
                } else {
                    $cityIdArray = Area::where('parent_id', $city_id)
                        ->where('path', 2)
                        ->select('area_id')
                        ->get()
                        ->pluck('area_id');
                    $query->whereIn('city_id', $cityIdArray);
                }
            })
            ->when($keyword, function(Builder $query) use ($keyword){
                $query->where(function (Builder $query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%")
                        ->orWhere('signboard_name', 'like', "%$keyword%");
                });
            })
            ->when($lng && $lat && $radius, function (Builder $query) use ($distances) {
                // 如果范围存在, 按距离搜索, 并按距离排序
                $query->whereIn('id', array_keys($distances));
            })
            ->when($lowestPrice || $highestPrice, function (Builder $query) use ($lowestPrice, $highestPrice){
                // 有价格限制时 按照价格区间筛选 并按照价格排序
                $query->when($lowestPrice && !$highestPrice, function (Builder $query) use ($lowestPrice) {
                    $query->where('lowest_amount', '>=', $lowestPrice);
                })
                    ->when($highestPrice, function (Builder $query) use ($lowestPrice, $highestPrice) {
                        $query->where('lowest_amount', '>=', $lowestPrice)
                            ->where('lowest_amount', '<', $highestPrice);
                    })
                    ->orderBy('lowest_amount');
            });

        if($lng && $lat){
            // 如果是按距离搜索, 需要在程序中按距离排序
            $allList = $query->select('id')->get();
            $total = $query->count();
            $list = $allList->map(function ($item) use ($lng, $lat, $user_key) {
                    $item->distance = Lbs::getDistanceOfCsMerchant($item->id, $user_key, floatval($lng), floatval($lat));
                    return $item;
                })
                ->sortBy('distance')
                ->forPage(request('page', 1), $pageSize)
                ->values()
                ->map(function($item) {
                    if ($item->is_pilot == 1) {
                        $item->distance -= 100000000;
                    }
                    $item->distance = Utils::getFormativeDistance($item->distance);
                    $merchant = DataCacheService::getCsMerchantDetail($item->id);
                    $merchant->distance = $item->distance;
                    // 格式化距离
                    return $merchant;
                });
        }else {
            // 没有按距离搜索时, 直接在数据库中排序并分页
            $data = $query->paginate($pageSize);
            $list = $data->items();
            $total = $data->total();
        }

        // 补充商家其他信息
        $list = collect($list);
        $list->each(function ($item){
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
            if($item->business_time) $item->business_time = json_decode($item->business_time, 1);
            // 兼容v1.0.0版客服电话字段
            $item->contacter_phone = $item->service_phone;
            // 商户评级字段，暂时全部默认为5星
            $item->grade = 5;

            //商户销量:
            $merchantOrderData = CsStatisticsMerchantOrder::where('cs_merchant_id',$item->id)->select('order_number_30d','order_number_today')->first();
            if($merchantOrderData){
                $item->month_order_number = $merchantOrderData->order_number_today + $merchantOrderData->order_number_30d;//月销售量加入当前销售量
            }else{
                $item->month_order_number = 0;
            }

            //配送信息:
            $MerchantInfo = CsMerchantSettingService::getDeliverSetting($item->id);
            if($MerchantInfo){
                $item->delivery_start_price = $MerchantInfo->delivery_start_price;
                $item->delivery_charges = $MerchantInfo->delivery_charges;
                $item->delivery_free_start = $MerchantInfo->delivery_free_start;
                $item->delivery_free_order_amount = $MerchantInfo->delivery_free_order_amount;
            }
            $item->city_limit = SettingService::getValueByKey('supermarket_city_limit');
            $item->show_city_limit = SettingService::getValueByKey('supermarket_show_city_limit');
            if(!isset($item->distance)){
                $item->distance = 0;//没有经纬度时,设置为0
            }

        });

        return ['list' => $list, 'total' => $total];
    }

    public static function userAppMerchantDetial($data)
    {
        $id = array_get($data,'id');
        $lng = array_get($data,'lng');
        $lat = array_get($data,'lat');

        $detail = CsMerchant::findOrFail($id);
        $detail->desc_pic_list = $detail->desc_pic_list ? explode(',', $detail->desc_pic_list) : [];
        if($detail->business_time) $detail->business_time = json_decode($detail->business_time, 1);
        if($lng && $lat){
            $currentUser = request()->get('current_user');
            $tempToken = empty($currentUser) ? str_random() : $currentUser->id;
            $distance = Lbs::getDistanceOfCsMerchant($id, $tempToken, floatval($lng), floatval($lat));
            // 格式化距离
            $detail->distance = Utils::getFormativeDistance($distance);
        }
        // 最低消费
        $detail->lowestAmount = CsMerchantService::getLowestPriceForMerchant($detail->id);
        // 兼容v1.0.0版客服电话字段
        $detail->contacter_phone = $detail->service_phone;

        return $detail;
    }

    /**
     * 根据商户名称获取商户某个字段的数组
     * @param $params
     * @param $field
     * @return Collection|\Illuminate\Support\Collection
     */
    public static function getMerchantColumnArrayByParams($params, $field)
    {
        $merchantName = array_get($params, 'merchantName', '');
        $operIds = array_get($params, 'operIds', []);

        $query = CsMerchant::query();
        if ($merchantName) {
            $query->where('name', 'like', "%$merchantName%");
        }
        if (!empty($operIds)) {
            if (is_array($operIds)) {
                $query->whereIn('oper_id', $operIds);
            } else {
                $query->where('oper_id', $operIds);
            }
        }
        $arr = $query->select($field)
            ->get()
            ->pluck($field);
        return $arr;
    }
}