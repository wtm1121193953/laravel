<?php

namespace App\Http\Controllers\Oper;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Area\Area;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantCategory;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MerchantController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList(Request $request)
    {
        $status = request('status');
        $data = Merchant::where('oper_id', $request->get('current_user')->oper_id)
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate();

        $data->each(function ($item){
            if ($item->merchant_category_id){
                $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
            }
            $item->account = MerchantAccount::where('merchant_id', $item->id)->first();
        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取全部列表
     */
    public function getAllList()
    {
        $status = request('status');
        $list = Merchant::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->get();

        return Result::success([
            'list' => $list,
        ]);
    }

    private function fillMerchantInfoFromRequest(Merchant $merchant)
    {
        $merchant->oper_id = request()->get('current_user')->oper_id;
        $merchant->merchant_category_id = request('merchant_category_id', 0);
        $merchant->name = request('name');
        $merchant->brand = request('brand','');
        $merchant->region = request('region');
        $merchant->province = request('province_id') ? Area::where('area_id', request('province_id'))->value('name') : '';
        $merchant->province_id = request('province_id', 0);
        $merchant->city = request('city_id') ? Area::where('area_id', request('city_id'))->value('name') : '';
        $merchant->city_id = request('city_id', 0);
        $merchant->area = request('area_id') ? Area::where('area_id', request('area_id'))->value('name') : '';
        $merchant->area_id = request('area_id', 0);
        $merchant->business_time = request('business_time');
        $merchant->logo = request('logo','');
        $merchant->desc_pic = request('desc_pic','');
        $merchant->desc = request('desc','');
        $merchant->invoice_title = request('invoice_title','');
        $merchant->invoice_no = request('invoice_no','');
        $merchant->status = request('status', 1);
        $merchant->lng = request('lng',0);
        $merchant->lat = request('lat',0);
        $merchant->address = request('address','');
        $merchant->contacter = request('contacter','');
        $merchant->contacter_phone = request('contacter_phone','');
        $merchant->settlement_cycle_type = request('settlement_cycle_type');
        $merchant->settlement_rate = request('settlement_rate');
        $merchant->business_licence_pic_url = request('business_licence_pic_url','');
        $merchant->organization_code = request('organization_code','');
        $merchant->tax_cert_pic_url = request('tax_cert_pic_url','');
        $merchant->legal_id_card_pic_a = request('legal_id_card_pic_a','');
        $merchant->legal_id_card_pic_b = request('legal_id_card_pic_b','');
        $merchant->contract_pic_url = request('contract_pic_url','');
        $merchant->licence_pic_url = request('licence_pic_url','');
        $merchant->hygienic_licence_pic_url = request('hygienic_licence_pic_url','');
        $merchant->agreement_pic_url = request('agreement_pic_url','');
        $merchant->bank_card_type = request('bank_card_type');
        $merchant->bank_open_name = request('bank_open_name','');
        $merchant->bank_card_no = request('bank_card_no','');
        $merchant->sub_bank_name = request('sub_bank_name','');
        $merchant->bank_open_address = request('bank_open_address','');
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
            'merchant_category_id' => 'required',
            'business_licence_pic_url' => 'required',
        ]);
        $merchant = new Merchant();

        $this->fillMerchantInfoFromRequest($merchant);

        $merchant->save();

        return Result::success($merchant);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'merchant_category_id' => 'required',
            'business_licence_pic_url' => 'required',
        ]);
        $merchant = Merchant::findOrFail(request('id'));

        $this->fillMerchantInfoFromRequest($merchant);

        if($merchant->audit_status == Merchant::AUDIT_STATUS_SUCCESS
            || $merchant->audit_status == Merchant::AUDIT_STATUS_RESUBMIT){
            $merchant->audit_status = Merchant::AUDIT_STATUS_RESUBMIT;
        }else {
            $merchant->audit_status = Merchant::AUDIT_STATUS_AUDITING;
        }

        $merchant->save();

        return Result::success($merchant);
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
        $merchant = Merchant::findOrFail(request('id'));
        $merchant->status = request('status');
        $merchant->save();

        $merchant->categoryPath = MerchantCategory::getCategoryPath($merchant->merchant_category_id);
        $merchant->account = MerchantAccount::where('merchant_id', $merchant->id)->first();

        return Result::success($merchant);
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
        $merchant = Merchant::findOrFail(request('id'));
        $merchant->delete();
        return Result::success($merchant);
    }

    /**
     * 创建商户账号
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function createAccount()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
            'account' => 'required',
            'password' => 'required|min:6',
        ]);
        $account = MerchantAccount::where('merchant_id', request('id'))->first();
        if(!empty($account)){
            throw new BaseResponseException('该商户账户已存在, 不能重复创建');
        }
        // 查询账号是否重复
        if(!empty(MerchantAccount::where('account', request('account'))->first())){
            throw new BaseResponseException('账号重复, 请更换账号');
        }
        $account = new MerchantAccount();

        $account->oper_id = request()->get('current_user')->oper_id;
        $account->account = request('account');
        $account->merchant_id = request('merchant_id');
        $salt = str_random();
        $account->salt = $salt;
        $account->password = MerchantAccount::genPassword(request('password'), $salt);
        $account->save();

        return Result::success($account);
    }

    public function editAccount()
    {

        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'merchant_id' => 'required|integer|min:1',
            'password' => 'required|min:6',
        ]);
        $account = MerchantAccount::findOrFail(request('id'));
        $account->merchant_id = request('merchant_id');
        $salt = str_random();
        $account->salt = $salt;
        $account->password = MerchantAccount::genPassword(request('password'), $salt);

        $account->save();

        return Result::success($account);
    }
}