<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Oper\OperMiniprogram;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

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
            'key' => 'required',
        ]);
        $miniprogram = new OperMiniprogram();
        $miniprogram->oper_id = request('oper_id');
        $miniprogram->name = request('name');
        $miniprogram->appid = request('appid', '');
        $miniprogram->secret = request('secret', '');
        $miniprogram->mch_id = request('mch_id', '');
        $miniprogram->key = request('key', '');

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
            'mch_id' => 'required',
            'key' => 'required',
        ]);
        $miniprogram = OperMiniprogram::findOrFail(request('id'));
        $miniprogram->oper_id = request('oper_id');
        $miniprogram->name = request('name');
        $miniprogram->appid = request('appid', '');
        $miniprogram->secret = request('secret', '');
        $miniprogram->mch_id = request('mch_id', '');
        $miniprogram->key = request('key', '');

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

    public function uploadCert()
    {
        $this->validate(request(), [
            'miniprogramId' => 'required|integer|min:1',
            'file' => 'required|file'
        ]);
        $miniprogramId = request('miniprogramId');
        $miniprogram = OperMiniprogram::where('id', $miniprogramId)->firstOrFail();

        $file = request()->file('file');
        if($file->extension() !== 'zip'){
            throw new BaseResponseException('请上传zip格式的证书文件');
        }

        $path = $file->storeAs("wxPayCert/$miniprogram->mch_id", 'cert.zip');

        // 解压缩文件
        $zip = new \ZipArchive();
        $absolutePath = storage_path('app/' . $path);
        if($zip->open($absolutePath) !== true){
            throw new BaseResponseException('解压缩文件错误');
        }
        $zip->extractTo(dirname($absolutePath));
        $zip->close();

        // 修改miniprogram中的证书路径
        $miniprogram->cert_zip_path = $path;
        $miniprogram->save();


        return Result::success([
            'path' => $path
        ]);
    }

}