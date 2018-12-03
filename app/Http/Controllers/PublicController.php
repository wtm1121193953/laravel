<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/11/22
 * Time: 12:18
 */

namespace App\Http\Controllers;


use App\Exceptions\BaseResponseException;
use App\Modules\Oper\Oper;
use App\Result;

class PublicController
{

    public function allOpers()
    {
        $key = '9570130f7ccc2f2b8bf1b7b861cce596';
        if(request()->get('key') != $key){
            throw new BaseResponseException('密钥错误');
        }
        $list = Oper::select('id', 'name')
            ->get();
        return Result::success(['list' => $list]);
    }
}