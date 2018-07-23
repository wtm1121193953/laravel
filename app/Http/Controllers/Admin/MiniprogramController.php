<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Oper\OperMiniprogramService;
use App\Result;

class MiniprogramController extends Controller
{

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
        $miniprogram = OperMiniprogramService::add(
            request('oper_id'),
            request('name'),
            request('appid', ''),
            request('secret', ''),
            request('mch_id', ''),
            request('key', ''),
            request('verify_file_path', '')
        );

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
        $miniprogram = OperMiniprogramService::edit(
            request('oper_id'),
            request('name'),
            request('appid', ''),
            request('secret', ''),
            request('mch_id', ''),
            request('key', ''),
            request('verify_file_path', '')
        );

        return Result::success($miniprogram);
    }

    public function uploadCert()
    {
        $this->validate(request(), [
            'miniprogramId' => 'required|integer|min:1',
            'file' => 'required|file'
        ]);
        $miniprogramId = request('miniprogramId');
        $file = request()->file('file');
        if($file->extension() !== 'zip'){
            throw new BaseResponseException('请上传zip格式的证书文件');
        }

        $miniprogram = OperMiniprogramService::uploadPayCert($miniprogramId, $file);

        return Result::success([
            'path' => $miniprogram->cert_zip_path
        ]);
    }

    /**
     * 上传服务器校验文件
     */
    public function uploadVerifyFile()
    {
        $file = request()->file('file');

        $path = OperMiniprogramService::uploadVerifyFile($file);

        return Result::success([
            'path' => $path
        ]);
    }
}