<?php

namespace App\Http\Controllers;


use App\Exceptions\BaseResponseException;
use App\Result;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use phpDocumentor\Reflection\File;

class UploadController
{

    public function image()
    {
        $file = request()->file('file');
        $content = file_get_contents($file->getRealPath());     // 文件流数据
        $cosPath = '';
        $extension = $file->getClientOriginalExtension();
        $name = self::makeName($content, $extension, $cosPath);
        if (!$name['status']) {
            $path = $file->storeAs($cosPath, $name['name'], 'cosv5');
        } else {
            $path = $cosPath . $name['name'];
        }
        $url = config('cos.cos_url') . '/' . $path;
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
     * @param   string $content 文件流数据
     * @param   string $fileType 文件类型
     * @param   string $path 上传路径
     * @return  array
     */
    public static function makeName( $content, $fileType, $path = '' )
    {
        $status = false;
        $name = md5($content) . '.' . $fileType;
        $disk = Storage::disk('cosv5');
        if ($disk->has($path . $name)) {
            // 处理重名
            $status = true;
            Log::info('这是个重名' . $name);
        }
        return ['status' => $status, 'name' => $name];
    }

    /**
     * apk文件上传
     * @return \Illuminate\Http\JsonResponse
     */
    public function file()
    {
        $file = request()->file('file');
        $name = $file->getClientOriginalName();

        $path = $file->storeAs('apk', $name, 'cosv5');

        $url = config('cos.cos_url') . '/' . $path;

        return Result::success([
            'url' => $url,
            'name' => $name,
            'size' => $file->getSize(),
        ]);
    }
}