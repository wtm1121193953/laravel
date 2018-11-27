<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Area\Area;
use App\ResultCode;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;


class CsUserAddressService extends BaseService {
    /**
     * 添加收获地址
     * @param $data
     */
    public static function addAddresses($data){
        $user = request()->get('current_user');
        $userAddress = new CsUserAddress();
        $userAddress->contacts = array_get($data,'contacts','');
        $userAddress->contact_phone = array_get($data,'contact_phone','');
        $userAddress->province_id =array_get($data,'province_id',0);
        $userAddress->user_id = $user->id;
        $userAddress->city_id = array_get($data,'city_id',0);
        $userAddress->area_id = array_get($data,'area_id',0);
        $userAddress->address = array_get($data,'address','');
        $userAddress->is_default = array_get($data,'is_default',0);

        $exist= CsUserAddress::where('contacts', $userAddress->contacts)
            ->where('contact_phone', $userAddress->contact_phone)
            ->where('province_id', $userAddress->province_id)
            ->where('user_id', $userAddress->user_id)
            ->where('city_id', $userAddress->city_id)
            ->where('area_id', $userAddress->area_id)
            ->where('address', $userAddress->address)
            ->exists();
        if( $exist )
        {
            throw new BaseResponseException('已添加过该地址', ResultCode::PARAMS_INVALID);
        }

        //查询省市区名称
        $userAddress->province = Area::getNameByAreaId($userAddress->province_id);
        if(!empty($userAddress->city_id)){
            $userAddress->city = Area::getNameByAreaId($userAddress->city_id);
        }
        else{
            $userAddress->city ='0';
        }
        if (!empty($userAddress->area_id)){
            $userAddress->area = Area::getNameByAreaId($userAddress->area_id);
        }
        else{
            $userAddress->area = '0';
        }

        if ($userAddress->is_default == CsUserAddress::DEFAULT){
            //设置默认
            $query  = CsUserAddress::where('user_id', $user->id )
                ->where("is_default", CsUserAddress::DEFAULT);
            if (!empty($query->get())){
                // 开启事务
                DB::beginTransaction();
                try{
                    // 全部改为未选
                    $query->update(['is_default'=>CsUserAddress::UNDEFAULT]);
                    DB::commit();
                }catch ( \Exception $e ){
                    DB::rollBack();
                    throw new BaseResponseException( $e->getMessage(),ResultCode::DB_INSERT_FAIL);
                }
            }
        }
        //保存地址
        if( !($userAddress->save()) ) {
            throw new BaseResponseException('新增失败', ResultCode::DB_INSERT_FAIL);
        }
    }

    /**
     * 获取地址列表
     * @param $isTestAddress
     * @param $cityId
     * @param $cityLimit
     * @return CsUserAddress[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getList($isTestAddress, $cityId='', $cityLimit=0){
        $user = request()->get('current_user');
        $list = CsUserAddress::where('user_id',$user->id)
            ->orderBy('is_default','desc')
            ->orderBy('id','desc')
            ->get();
        foreach ($list as $item){
            if ($item->city_id == 0){
                $item->city = '';
            }
            if ($item->area_id == 0){
                $item->area = '';
            }
        }
        if ($cityLimit == 0 || $isTestAddress == 0){
            return $list;
        } else{
            if (empty($cityId)){
                throw new BaseResponseException('未选择商家', ResultCode::PARAMS_INVALID);
            }
            $list->each(function ($item){
                if ($item->city_id == request('city_id')){
                    $item->outRadius = '0';
                }
                else{
                    $item->outRadius = '1';
                }
            });
            return $list;
        }
    }

    /**
     * 编辑地址
     */
    public static function editAddress($data){
        $user = request()->get('current_user');

        $userAddress = CsUserAddress::where('user_id',$user->id)
            ->where('id',$data['id'])
            ->first();
         if (!empty($data['contacts']))  {
             $userAddress->contacts = $data['contacts'];
         }
        $userAddress->contacts = array_get($data,'contacts',$userAddress->contacts);
        $userAddress->contact_phone = array_get($data,'contact_phone',$userAddress->contact_phone);
        $userAddress->province_id =array_get($data,'province_id',$userAddress->province_id);

        $userAddress->city_id = array_get($data,'city_id',$userAddress->city_id);
        $userAddress->area_id = array_get($data,'area_id',$userAddress->area_id);
        $userAddress->address = array_get($data,'address',$userAddress->address);
        $userAddress->is_default = array_get($data,'is_default',$userAddress->is_default);


        if (!empty($data['province_id']))  {
            $userAddress->province_id = $data['province_id'];
            $userAddress->province = Area::getNameByAreaId($userAddress->province_id);
        }
        if (!empty($data['city_id']))  {
            $userAddress->city_id = $data['city_id'];
            $userAddress->city = Area::getNameByAreaId($userAddress->city_id);
        }
        else{
            $userAddress->city_id = '0';
            $userAddress->city = '0';
        }
        if (!empty($data['area_id']))  {
            $userAddress->area_id = $data['area_id'];
            $userAddress->area = Area::getNameByAreaId($userAddress->area_id);
        }
        else{
            $userAddress->area_id = '0';
            $userAddress->area = '0';
        }
        if ($userAddress->is_default == CsUserAddress::DEFAULT){
            //设置默认
            $query  = CsUserAddress::where('user_id', $user->id )
                ->where("is_default", CsUserAddress::DEFAULT);
            if (!empty($query->get())){
                // 开启事务
                DB::beginTransaction();
                try{
                    // 全部改为未选
                    $query->update(['is_default'=>CsUserAddress::UNDEFAULT]);
                    DB::commit();
                }catch ( \Exception $e ){
                    DB::rollBack();
                    throw new BaseResponseException( $e->getMessage(),ResultCode::DB_INSERT_FAIL);
                }
            }
        }
        //保存地址
        if( !($userAddress->save()) ) {
            throw new BaseResponseException('更新失败', ResultCode::DB_INSERT_FAIL);
        }
    }

    /**
     * 删除收货地址
     * @throws \Exception
     */
    public static function delAddress($id){
        $user = request()->get('current_user');
      $res =  CsUserAddress::where('user_id',$user->id)
          ->where('id',$id)
          ->delete();
        if(!$res)
        {
            throw new BaseResponseException('删除失败',ResultCode::DB_DELETE_FAIL);
        }
    }

}