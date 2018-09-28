<?php

namespace App\Http\Controllers;


use App\Result;
use Intervention\Image\Facades\Image;

class UploadController
{

    public function image()
    {
        $file = request()->file('file');
        $path = $file->store('/image/item', 'public');
        $url = asset('storage/' . $path);

        $image = Image::make($url);

        return Result::success([
            'url' => $url,
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * apk文件上传
     * @return \Illuminate\Http\JsonResponse
     */
    public function file()
    {
        $file = request()->file('file');
        $name = $file->getClientOriginalName();

        $path = $file->storeAs('/apk', $name, 'public');
        $url = asset('storage/' .$path);

        return Result::success([
            'url' => $url,
            'name' => $name,
            'size' => $file->getSize(),
        ]);
    }
}