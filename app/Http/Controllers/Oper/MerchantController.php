<?php

namespace App\Http\Controllers\Oper;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantCategory;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class MerchantController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $data = Merchant::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();

        $data->each(function ($item){
            $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
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
        $merchant->name = request('name');
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);
        $merchant = new Merchant();
        $merchant->name = request('name');
        $merchant->status = request('status', 1);

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
        ]);
        $merchant = Merchant::findOrFail(request('id'));
        $merchant->name = request('name');
        $merchant->status = request('status', 1);

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