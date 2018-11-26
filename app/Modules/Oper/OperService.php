<?php

namespace App\Modules\Oper;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Modules\Area\Area;
use App\Modules\Tps\TpsBind;
use App\Modules\Tps\TpsBindService;
use Illuminate\Database\Eloquent\Builder;

class OperService extends BaseService
{

    /**
     * @param $id
     * @param array $fields
     * @return Oper
     */
    public static function getById($id, $fields = ['*'])
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        $oper = Oper::find($id, $fields);
        return $oper;
    }

    public static function getNameById($id)
    {
        $name = Oper::where('id', $id)->value('name');
        return $name;
    }

    /**
     * 获取运营中心列表, 包含运营中心的账号信息, 小程序信息, 以及绑定的tps账号信息
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($params)
    {
        $name = array_get($params, 'name');
        $status = array_get($params, 'status');
        $tel = array_get($params, 'tel');
        $payToPlatform = array_get($params, 'payToPlatform');
        if($payToPlatform ==3){$payToPlatform1 = true;} else{$payToPlatform1 = NULL;}
        if($payToPlatform ==1){$payToPlatform2 = true;} else{$payToPlatform2 = NULL;}
        if($payToPlatform ==2){$payToPlatform3 = true;} else{$payToPlatform3 = NULL;}

        $data = Oper::when($status, function (Builder $query) use ($status) {
            $query->where('status', $status);
        })
            ->when($name, function (Builder $query) use ($name) {
                $query->where('name', 'like', "%$name%");
            })
            ->when($tel, function (Builder $query) use ($tel) {
                $query->where('tel', 'like', "%$tel%");
            })
            ->when($payToPlatform1, function (Builder $query) {
                $query->where('pay_to_platform', 0);
            })
            ->when($payToPlatform2, function (Builder $query){
                $query->where('pay_to_platform', 1);
            })
            ->when($payToPlatform3, function (Builder $query){
                $query->where('pay_to_platform', 2);
            })
            ->orderBy('id', 'desc')
            ->paginate();

        $data->each(function ($item) {
            $item->account = OperAccountService::getByOperId($item->id) ?: null;
            $item->miniprogram = OperMiniprogramService::getByOperId($item->id) ?: null;
            $item->bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($item->id, TpsBind::ORIGIN_TYPE_OPER) ?: null;
        });

        return $data;
    }

    /**
     * 获取全部的运营中心列表, 可指定字段
     * @param $params
     * @param array $fields
     * @param bool $base 是否只获取基础信息, 若为false, 返回的字段中包含运营中心的账号信息, 小程序信息, 以及绑定的tps账号信息
     * @return Oper[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAll($params, $fields = ['*'], $base = true)
    {
        if(is_string($fields)){
            $fields = explode(',', $fields);
        }
        $name = array_get($params, 'name');
        $status = array_get($params, 'status');
        $tel = array_get($params, 'tel');
        $contacter = array_get($params, 'contacter');
        $provinceId = array_get($params, 'province_id');
        $cityId = array_get($params, 'city_id');

        $data = Oper::when($status, function (Builder $query) use ($status) {
                    $query->where('status', $status);
                })
                ->when($name, function (Builder $query) use ($name) {
                    $query->where('name', 'like', "%$name%");
                })
                ->when($tel, function (Builder $query) use ($tel) {
                    $query->where('tel', 'like', "%$tel%");
                })
                ->when($contacter, function (Builder $query) use ($contacter) {
                    $query->where('contacter', 'like', "%$contacter%");
                })
                ->when($provinceId, function (Builder $query) use ($provinceId) {
                    $query->where('province_id', $provinceId);
                })
                ->when($cityId, function (Builder $query) use ($cityId) {
                    $query->where('city_id', $cityId);
                })
                ->orderBy('id', 'desc')
                ->select($fields)
                ->get();

        if (!$base) {
            $data->each(function ($item) {
                $item->account = OperAccountService::getByOperId($item->id) ?: null;
                $item->miniprogram = OperMiniprogramService::getByOperId($item->id) ?: null;
                $item->bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($item->id, TpsBind::ORIGIN_TYPE_OPER) ?: null;
            });
        }

        return $data;
    }

    /**
     * 获取运营中心详情
     * @param $id
     * @return Oper
     */
    public static function detail($id)
    {
        $oper = Oper::findOrFail($id);
        $oper->account = OperAccountService::getByOperId($oper->id) ?: null;
        $oper->miniprogram = OperMiniprogramService::getByOperId($oper->id) ?: null;
        return $oper;
    }

    /**
     * 添加运营中心
     * @return Oper
     */
    public static function addFromRequest()
    {
        $oper = new Oper();
        $oper->name = request('name');
        $oper->number = request('number','');
        $oper->status = request('status', 1);
        $oper->contacter = request('contacter', '');
        $oper->tel = request('tel', '');
        $provinceId = request('province_id', 0);
        $cityId = request('city_id', 0);
        $oper->province_id = $provinceId;
        $oper->city_id = $cityId;
        $oper->province = Area::where('area_id', $provinceId)->value('name');
        $oper->city = Area::where('area_id', $cityId)->value('name');
        $oper->address = request('address', '');
        $oper->email = request('email', '');
        $oper->legal_name = request('legal_name', '');
        $oper->legal_id_card = request('legal_id_card', '');
        $oper->invoice_type = request('invoice_type', 0);
        $oper->invoice_tax_rate = request('invoice_tax_rate', 0);
//        $oper->settlement_cycle_type = request('settlement_cycle_type', 1);
        $oper->bank_card_no = request('bank_card_no', '');
        $oper->sub_bank_name = request('sub_bank_name', '');
        $oper->bank_open_name = request('bank_open_name', '');
        $oper->bank_open_address = request('bank_open_address', '');
        $oper->bank_code = request('bank_code', '');
        $oper->licence_pic_url = request('licence_pic_url', '');
        $oper->business_licence_pic_url = request('business_licence_pic_url', '');
        $oper->contact_qq = request('contact_qq', '');
        $oper->contact_wechat = request('contact_wechat', '');
        $oper->contact_mobile = request('contact_mobile', '');

        $oper->save();
        return $oper;
    }

    /**
     * @param $id
     * @return Oper
     */
    public static function editFromRequest($id)
    {

        $oper = Oper::find($id);
        if (empty($oper)) {
            throw new DataNotFoundException('运营中心信息不存在');
        }
        $oper->name = request('name');
        $oper->number = request('number','');
        $oper->status = request('status', 1);
        $oper->contacter = request('contacter', '');
        $oper->tel = request('tel', '');
        $provinceId = request('province_id', 0);
        $cityId = request('city_id', 0);
        $oper->province_id = $provinceId;
        $oper->city_id = $cityId;
        $province = Area::where('area_id', $provinceId)->value('name');
        if (!$province) {
            throw new BaseResponseException('请选择省份');
        }
        $oper->province = $province;
        $city = Area::where('area_id', $cityId)->value('name');
        if (!$city) {
            throw new BaseResponseException('请选择城市');
        }
        $oper->city = $city;
        $oper->address = request('address', '');
        $oper->email = request('email', '');
        $oper->legal_name = request('legal_name', '');
        $oper->legal_id_card = request('legal_id_card', '');
        $oper->invoice_type = request('invoice_type', 0);
        $oper->invoice_tax_rate = request('invoice_tax_rate', 0);
//        $oper->settlement_cycle_type = request('settlement_cycle_type', 1);
        $oper->bank_card_no = request('bank_card_no', '');
        $oper->sub_bank_name = request('sub_bank_name', '');
        $oper->bank_open_name = request('bank_open_name', '');
        $oper->bank_open_address = request('bank_open_address', '');
        $oper->bank_code = request('bank_code', '');
        $oper->licence_pic_url = request('licence_pic_url', '');
        $oper->business_licence_pic_url = request('business_licence_pic_url', '');
        $oper->contact_qq = request('contact_qq', '');
        $oper->contact_wechat = request('contact_wechat', '');
        $oper->contact_mobile = request('contact_mobile', '');

        $oper->save();

        return $oper;
    }

    /**
     * 更新状态
     * @param $id
     * @param $status
     * @return Oper
     */
    public static function changeStatus($id, $status)
    {

        $oper = Oper::find($id);
        if (empty($oper)) {
            throw new DataNotFoundException('运营中心信息不存在');
        }
        $oper->status = $status;

        $oper->save();
        return $oper;
    }

    /**
     * 更新小程序支付对象设置
     * @param $id
     * @param $pay_to_platform
     * @return Oper
     */
    public static function changePayToPlatform($id, $pay_to_platform)
    {

        $oper = Oper::find($id);
        if (empty($oper)) {
            throw new DataNotFoundException('运营中心信息不存在');
        }
        $oper->pay_to_platform = $pay_to_platform;

        $oper->save();
        return $oper;
    }

    /**
     * 切换运营中心支付到平台
     * @param $id
     * @return Oper
     */
    public static function switchPayToPlatform($id)
    {
        $oper = Oper::find($id);
        if (empty($oper)) {
            throw new DataNotFoundException('运营中心信息不存在');
        }
        $oper->pay_to_platform = 1;
        $oper->save();
        return $oper;
    }


    /**
     * 根据运营中心名获取运营中心某个字段的数组
     * @param $operName
     * @param $field
     * @return \Illuminate\Support\Collection
     */
    public static function getOperColumnArrayByOperName($operName, $field)
    {
        $arr = Oper::where('name', 'like', "%$operName%")
            ->select($field)
            ->get()
            ->pluck($field);
        return $arr;
    }

    public static function allOpers()
    {
        return Oper::select('id','name')->get()->toArray();
    }

    /**
     * 获取所有合作的运营商
     * @param bool $withQuery
     * @return Oper|array
     */
    public static function allNormalOpers($withQuery = false)
    {
        $query = Oper::select('id','name')->where('status','=',Oper::STATUS_NORMAL);
        if ($withQuery) {
            return $query;
        } else {
            return $query->get()->toArray();
        }
    }

    /**
     * 修改oper和oper_bizer中的比例
     * @param $operId
     * @param $bizerDivide
     * @return Oper
     */
    public static function setOperBizerDivide($operId, $bizerDivide)
    {
        $oper = self::getById($operId);
        $oper->bizer_divide = number_format($bizerDivide, 2);
        $oper->save();

        OperBizer::chunk(1000, function ($operBizers) use ($operId, $bizerDivide) {
            foreach ($operBizers as $operBizer) {
                if ($operBizer->oper_id == $operId) {
                    $operBizer->divide = number_format($bizerDivide, 2);
                    $operBizer->save();
                }
            }
        });

        return $oper;
    }

    /**
     * 通过operId 获取相关数组
     * @param $operId
     * @param $field
     * @return \Illuminate\Support\Collection
     */
    public static function getOperColumnArrayByOperId($operId, $field)
    {
        $arr = Oper::where('id', $operId)
            ->select($field)
            ->get()
            ->pluck($field);
        return $arr;
    }
}
