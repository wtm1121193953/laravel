<?php

namespace App\Http\Controllers;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Merchant\MerchantService;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Modules\Wechat\WechatService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadController extends Controller
{

    public function download()
    {
        $code = request('code', 'normal');
        switch ($code){
            case 'normal': // 普通下载, 通过文件url或path下载
                return $this->normalDownload();
            case 'merchant_pay_app_code':
                return $this->downloadMerchantPayAppCode();
            case 'doc':
                return $this->downloadDoc();
            default:
                abort(404);
        }
    }

    /**
     * 普通下载, 通过文件url或path下载
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function normalDownload()
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
            return Storage::download($path, $as);
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
        if(!Storage::exists($path)){
            // 不是storage存储返回的路径时, 尝试使用绝对路径获取
            if(file_exists($path)){
                return response()->download($path, $as);
            }
            throw new BaseResponseException('要下载的文件不存在');
        }
        return Storage::download($path, $as);
    }

    /**
     * 商户支付二维码下载
     */
    private function downloadMerchantPayAppCode()
    {
        $this->validate(request(), [
            'merchantId' => 'required|integer|min:1'
        ]);
        $type = request('type', 1);
        $merchantId = request('merchantId');

        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);

        $scene = MiniprogramSceneService::getPayAppCodeByMerchantId($merchantId);

        $filePath = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);

        $signboardName = MerchantService::getSignboardNameById($merchantId);

        WechatService::addNameToAppCode($filePath, $signboardName);

        return response()->download($filePath, '支付小程序码_' . ['', '小', '中', '大'][$type] . '.jpg');
    }

    /**
     * doc 文件下载
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    private function downloadDoc()
    {
        $path = request('path') ?? request('url');
        $as = request('as');
        if(empty($path)){
            throw new ParamInvalidException();
        }
        if(empty($as)){
            $as = basename($path);
        }

        if (!Storage::exists($path)) {
            throw new BaseResponseException('要下载的文件不存在');
        }
        $response = response(file_get_contents($path));
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $as .'"');
        return $response;
    }
}