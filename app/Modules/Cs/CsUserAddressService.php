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
use App\Result;
use App\ResultCode;
use Illuminate\Support\Facades\DB;


class CsUserAddressService extends BaseService {
    /**
     * 添加收获地址
     * @author zwg
     * @param $data 传入数据
     */
    public static function addAddresses($data){
        $user = request()->get('current_user');
        $userAddress = new CsUserAddress();
        $userAddress->contacts = $data['contacts'];
        $userAddress->contact_phone = $data['contact_phone'];
        $userAddress->province_id = $data['province_id'];
        $userAddress->user_id = $user->id;
        $userAddress->city_id = $data['city_id'];
        $userAddress->area_id = $data['area_id'];
        $userAddress->address = $data['address'];
        $userAddress->is_default = $data['is_default'];

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
        $userAddress->province = Area::findOrFail($userAddress->province_id)->name;
        $userAddress->city = Area::findOrFail($userAddress->city_id)->name;
        $userAddress->area = Area::findOrFail($userAddress->area_id)->name;

        if ($userAddress->is_default == CsUserAddress::DEFAULT){
            //设置默认
            $query  = CsUserAddress::where('user_id', $user->id )
                ->where("is_default", CsUserAddress::DEFAULT);
            if (sizeof($query) > 0){
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
     * @param $city_wide
     * @return CsUserAddress[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getList($isTestAddress,$city_wide){
        $user = request()->get('current_user');
        $list = CsUserAddress::where('user_id',$user->id)->get();
        if ($city_wide == 0 || $isTestAddress == 0){
            return $list;
        }
        else{
            if (empty(request('city_id'))){
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
        if (!empty($data['contact_phone']))  {
            $userAddress->contact_phone = $data['contact_phone'];
        }
        if (!empty($data['province_id']))  {
            $userAddress->province_id = $data['province_id'];
            $userAddress->province = Area::findOrFail($userAddress->province_id)->name;
        }
        if (!empty($data['city_id']))  {
            $userAddress->city_id = $data['city_id'];
            $userAddress->city = Area::findOrFail($userAddress->city_id)->name;
        }
        if (!empty($data['area_id']))  {
            $userAddress->area_id = $data['area_id'];
            $userAddress->area = Area::findOrFail($userAddress->area_id)->name;
        }
        if (!empty($data['address']))  {
            $userAddress->address = $data['address'];
        }
        if (!empty($data['is_default']))  {
            $userAddress->is_default = $data['is_default'];
        }
        if ($userAddress->is_default == CsUserAddress::DEFAULT){
            //设置默认
            $query  = CsUserAddress::where('user_id', $user->id )
                ->where("is_default", CsUserAddress::DEFAULT);
            if (sizeof($query) > 0){
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
     */
    public static function delAddress($id){
        $user = request()->get('current_user');
      $res =  CsUserAddress::where('user_id',$user->id)
          ->where('id',$id)
          ->delete();
        if(!$res)
        {
            throw new BaseResponseException('删除失败',ResultCode::DB_INSERT_FAIL);
        }
    }

}