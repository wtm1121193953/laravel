<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 15:59
 */

namespace App\Modules\Oper;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\ResultCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Modules\Bizer\Bizer;


class OperBizerService extends BaseService
{

    /**
     * 根据业务员获取运营中心
     * @param type $file
     * @return string
     * @throws BaseResponseException
     */
    public static function getBizerOper(array $data, bool $getWithQuery = false)
    {
        $bizer_id = array_get($data,"bizer_id");
        // 全局限制条件
        $query = OperBizer::where('status', 1)->orderByDesc('id');
        if(!empty($bizer_id)){
            $query->where("bizer_id",$bizer_id);
        }
        if ($getWithQuery) {
            return $query;
        } else {
            $data = $query->paginate();
            //print_r($data);exit;
            $data->each(function ($item) {
                //echo $item->bizer_id;exit;
                $item->operName = Oper::where('id', $item->oper_id)->value('name');
                //$item->bizerName = Bizer::where('bizer_id', $item->bizer_id)->value('name');
            });
            return $data;
        }
    }
    
}