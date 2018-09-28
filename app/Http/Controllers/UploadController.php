<?php

namespace App\Http\Controllers;


use App\Result;
use Intervention\Image\Facades\Image;

class UploadController
{

    public function image()
    {
        $file = request()->file('file');
//        $path = $file->store('/image/item', 'public');
        $path = $file->store('/image/item', 'cosv5');
//        $url = asset('storage/' . $path);
        $url = 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/'.$path;
        $image = Image::make($url);

        return Result::success([
            'url' => $url,
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'size' => $file->getSize(),
        ]);
    }
}