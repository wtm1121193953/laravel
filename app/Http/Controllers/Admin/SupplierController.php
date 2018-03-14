<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/14
 * Time: 21:54
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Supplier\Supplier;
use App\Result;

class SupplierController extends Controller
{

    public function getList()
    {
        $data = Supplier::orderBy('id', 'desc')->paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);
        $supplier = new Supplier();
        $supplier->name = request('name');
        $supplier->status = request('status', 1);

        $supplier->save();

        return Result::success($supplier);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $supplier = Supplier::findOrFail(request('id'));
        $supplier->name = request('name');
        $supplier->status = request('status', 1);

        $supplier->save();

        return Result::success($supplier);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
        ]);
        $supplier = Supplier::findOrFail(request('id'));
        $supplier->status = request('status');

        $supplier->save();
        return Result::success($supplier);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $supplier = Supplier::findOrFail(request('id'));
        $supplier->delete();
        return Result::success($supplier);
    }

}