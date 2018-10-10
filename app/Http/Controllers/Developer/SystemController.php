<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/10/6
 * Time: 18:07
 */

namespace App\Http\Controllers\Developer;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Result;

class SystemController extends Controller
{

    /**
     * 系统命令执行
     */
    public function command()
    {
        $command = request('command');
        if(!in_array($command, [
            'queue:restart',
        ])){
            throw new BaseResponseException();
        }
        $rootPath = base_path();
        $result = `cd $rootPath && php artisan $command`;
        return Result::success($result, compact('rootPath', 'result'));
    }

    /**
     * 执行系统原生命令
     */
    public function nativeCommand()
    {
        set_time_limit(0);
        $command = request('command');
        if(!in_array($command, [
            'git pull',
            'npm run dev',
            'npm run prod',
        ])){
            throw new BaseResponseException();
        }
        $rootPath = base_path();
        $result = `cd $rootPath && $command`;
        return Result::success($result, compact('rootPath', 'result'));
    }
}