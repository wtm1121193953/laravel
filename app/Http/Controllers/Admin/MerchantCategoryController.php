<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/18
 * Time: 22:49
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategory;
use App\Result;

class MerchantCategoryController extends Controller
{

    public function getTree()
    {
        $tree = MerchantCategory::getTree();
        return Result::success(['list' => $tree]);
    }

    public function getList()
    {
        $data = MerchantCategory::paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required'
        ]);
        $category = new MerchantCategory();
        $category->name = request('name');
        $category->status = request('status', 1);
        $category->pid = request('pid', 0);
        $category->save();

        MerchantCategory::clearCache();

        return Result::success($category);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $category = MerchantCategory::find(request('id'));
        if(empty($category)){
            throw new ParamInvalidException('类目不存在或已被删除');
        }
        $category->name = request('name');
        $category->status = request('status', 1);
        $category->pid = request('pid', 0);
        $category->save();

        MerchantCategory::clearCache();

        return Result::success($category);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer|in:1,2',
        ]);
        $category = MerchantCategory::find(request('id'));
        if(empty($category)){
            throw new ParamInvalidException('类目不存在或已被删除');
        }
        $category->status = request('status', 1);
        $category->save();

        MerchantCategory::clearCache();

        return Result::success($category);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);

        $category = MerchantCategory::find(request('id'));
        if(empty($category)){
            throw new ParamInvalidException('类目不存在或已被删除');
        }

        // 判断该类别下是否有子类别
        if( !empty( MerchantCategory::where('pid', $category->id)->first() ) ){
            throw new ParamInvalidException('该类目下存在子类目, 请先删除子类目再删除该类目');
        }
        if( !empty( $merchant = Merchant::where('merchant_category_id', $category->id)->first() ) ){
            throw new ParamInvalidException('该类目下存在商家 ' . $merchant->name . ' 等, 请先修改商家所属类目信息');
        }

        $category->delete();

        MerchantCategory::clearCache();

        return Result::success($category);
    }
}