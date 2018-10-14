<?php

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Country\CountryServer;
use App\Result;

class CountryController extends Controller
{
    public function getList()
    {
        $list = CountryServer::getAll();
        return Result::success(['list' => $list]);
    }
}