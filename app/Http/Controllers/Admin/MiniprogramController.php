<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Oper\OperMiniprogram;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class MiniprogramController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $data = OperMiniprogram::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();

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
        $list = OperMiniprogram::when($status, function (Builder $query) use ($status){
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
            'oper_id' => 'required|integer|min:1',
            'name' => 'required',
            'appid' => 'required',
            'secret' => 'required',
            'mch_id' => 'required',
        ]);
        $miniprogram = new OperMiniprogram();
        $miniprogram->oper_id = request('oper_id');
        $miniprogram->name = request('name');
        $miniprogram->appid = request('appid', '');
        $miniprogram->secret = request('secret', '');
        $miniprogram->mch_id = request('mch_id', '');

        $miniprogram->save();

        return Result::success($miniprogram);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'oper_id' => 'required|integer|min:1',
            'name' => 'required',
            'appid' => 'required',
            'secret' => 'required',
        ]);
        $miniprogram = OperMiniprogram::findOrFail(request('id'));
        $miniprogram->oper_id = request('oper_id');
        $miniprogram->name = request('name');
        $miniprogram->appid = request('appid', '');
        $miniprogram->secret = request('secret', '');

        $miniprogram->save();

        return Result::success($miniprogram);
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
        $miniprogram = OperMiniprogram::findOrFail(request('id'));
        $miniprogram->status = request('status');

        $miniprogram->save();
        return Result::success($miniprogram);
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
        $miniprogram = OperMiniprogram::findOrFail(request('id'));
        $miniprogram->delete();
        return Result::success($miniprogram);
    }

}