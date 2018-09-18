<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/14/014
 * Time: 15:10
 */

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Bank\Bank;
use App\Modules\Bank\BankService;
use App\Result;
use Illuminate\Support\Facades\Log;

class BankController extends Controller
{

    public function getList()
    {
        $name = request('name');
        $status = request('status');


        $startTime = microtime(true);
        $data = BankService::getList([
            'name' => $name,
            'status' => $status,
        ]);
        $endTime = microtime(true);

        Log::debug('耗时: ', ['start time' => $startTime, 'end time' => $endTime, '耗时: ' => $endTime - $startTime]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     *
     */
    public function add()
    {
        $name = request('name');
        $this->validate(request(),[
            'name'  =>  'required|unique:banks,name'
        ],[
            'name.required'  => '银行名不可为空',
            'name.unique'    => '银行名不可重复'
        ]);
        $rt = 0;
        if ($name) {
            $obj = new Bank();
            $obj->name = $name;
            $obj->status = 1;
            $rt = $obj->save();
        }
        return Result::success();
     }

     public function del()
     {
         $id = request('id');
         $bank = Bank::findOrFail($id);
         $bank->delete();
         return Result::success();
     }

     public function edit()
     {
         $id = request('id');
         $name = request('name');
         $existBankName = Bank::where('name',$name)
                                ->where('id','!=',$id)
                                ->exists();
         if($existBankName){
             throw new BaseResponseException('银行名不能相同');
         }
         $bank = Bank::findOrFail($id);
         $bank->name = $name;
         $bank->save();
         return Result::success();
     }


    /**
     * 修改状态
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
        ]);
        $oper = BankService::changeStatus(request('id'), request('status'));

        return Result::success($oper);
    }
}