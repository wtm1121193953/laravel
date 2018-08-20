<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/5
 * Time: 20:02
 */

namespace App\Http\Controllers;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadController extends Controller
{

    public function download()
    {
        $path = request('path') ?? request('url');
        $as = request('as');
        if(empty($path)){
            throw new ParamInvalidException();
        }
        if(empty($as)){
            $as = basename($path);
        }
        if(Str::startsWith($path, 'storage://')){
            $path = Str::replaceFirst('storage://', '', $path);
            if(!Storage::exists($path)){
                throw new BaseResponseException('要下载的文件不存在');
            }
            return Storage::download($path);
        }else if(Str::startsWith($path, 'http://') || Str::startsWith($path, 'https://')){
            $c = new Client();
            $tempFilename = Str::random();
            $data = $c->get($path)->getBody()->getContents();
            $dir = storage_path('app/download/temp/' . date('Y-m-d') );
            if(!is_dir($dir)){
                mkdir($dir, 0755, true);
            }
            file_put_contents($dir . '/' . $tempFilename, $data);

            return response()->download($dir . '/' . $tempFilename, $as);
        }
        if(!file_exists($path) && !Storage::exists($path)){
            throw new BaseResponseException('要下载的文件不存在');
        }
        return Storage::download($path, $as);
    }
}