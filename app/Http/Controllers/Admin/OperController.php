<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Area\Area;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperAccount;
use App\Modules\Oper\OperMiniprogram;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class OperController extends Controller
{

    /**
     * @author  xianghua
     * 2018/6/28
     * 获取列表
     */
    public function getList()
    {
        $name = request('name');
        $status = request('status');
        $tel = request('tel');

        $data = Oper::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })
            ->when($name, function(Builder $query) use ($name){
                $query->where('name', 'like', "%$name%");
            })
            ->when($tel, function(Builder $query) use ($tel){
                $query->where('tel', 'like', "%$tel%");
            })
            ->orderBy('id', 'desc')
            ->paginate();

        $data->each(function ($item){
            $item->account = OperAccount::where('oper_id', $item->id)->first() ?: null;
            $item->miniprogram = OperMiniprogram::where('oper_id', $item->id)->first() ?: null;
        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $oper = Oper::findOrFail(request('id'));
        $oper->account = OperAccount::where('oper_id', $oper->id)->first() ?: null;
        $oper->miniprogram = OperMiniprogram::where('oper_id', $oper->id)->first() ?: null;
        return Result::success($oper);
    }

    /**
     * 获取全部列表
     */
    public function getAllList()
    {
        $status = request('status');
        $list = Oper::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->get();

        return Result::success([
            'list' => $list,
        ]);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
        ]);
        $oper = new Oper();
        $oper->name = request('name');
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

        $oper->save();

        return Result::success($oper);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
        ]);
        $oper = Oper::findOrFail(request('id'));
        $oper->name = request('name');
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

        $oper->save();

        return Result::success($oper);
    }

    /**
     * 修改状态
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
        ]);
        $oper = Oper::findOrFail(request('id'));
        $oper->status = request('status');

        $oper->save();
        return Result::success($oper);
    }

    /**
     * 删除
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $oper = Oper::findOrFail(request('id'));
        $oper->delete();
        return Result::success($oper);
    }

    /**
     * 支付到平台状态修改
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setPayToPlatformStatus()
    {
        $oper = Oper::findOrFail(request('id'));
        $oper->pay_to_platform = 1;
        $oper->save();

        return Result::success($oper);
    }

}