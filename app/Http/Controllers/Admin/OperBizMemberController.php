<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperBizMember;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class OperBizMemberController extends Controller
{
    /**
     * 搜索业务员
     */
    public function search()
    {
        $this->validate(request(), [
            'operId' => 'required|integer|min:1',
        ]);
        $code = request('code', '');
        $name = request('name', '');
        $mobile = request('mobile', '');
        $keyword = request('keyword', '');
        $status = request('status');
        $operId = request('operId');

        $list = OperBizMember::where('oper_id', $operId)
            ->when($status, function(Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when(!empty($keyword), function(Builder $query) use ($keyword){
                $query->where(function(Builder $query) use ($keyword){
                    $query->where('code', 'like', "%$keyword%")
                        ->orWhere('name', 'like', "%$keyword%")
                        ->orWhere('mobile', 'like', "%$keyword%");
                });
            })
            ->when(!empty($code), function(Builder $query) use ($code) {
                $query->where('code', 'like', "%$code%");
            })
            ->when(!empty($name), function(Builder $query) use ($name){
                $query->where('name', 'like', "%$name%");
            })
            ->when(!empty($mobile), function(Builder $query) use ($mobile){
                $query->where('mobile', 'like', "%$mobile%");
            })->get();
        return Result::success([
            'list' => $list
        ]);
    }

}