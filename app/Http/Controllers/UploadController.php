<?php

namespace App\Http\Controllers;


use App\Exceptions\BaseResponseException;
use App\Result;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadController
{

    public function image()
    {
        $file = request()->file('file');
        $name = self::makeName($file->getClientOriginalExtension(),config('cos.default_image_save_path'));
        $path = $file->storeAs(config('cos.default_image_save_path'),$name , 'cosv5');
        $url = 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/'.$path;
        $image = Image::make($url);

        return Result::success([
            'url' => $url,
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * 生成文件名
     * Author:   JerryChan
     * Date:     2018/9/29 10:31
     * @param   string      $fileType   文件类型
     * @param   string      $path       上传路径
     * @param   int         $num        递归次数统计
     * @return  string
     */
    public static function makeName($fileType,$path='',$num=0){
        if($num>10){
            throw new BaseResponseException('生成文件名异常，终止执行');
        }
        $name = md5(uniqid() . str_random(32)).'.'.$fileType;
        $disk = Storage::disk('cosv5');
        if($disk->has($path.$name)){
            // 处理重名
            $name = self::makeName($fileType,$path,++$num);
        }
        return $name;
    }
}