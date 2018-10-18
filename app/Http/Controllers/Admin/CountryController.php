<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Country\CountryService;
use App\Result;

class CountryController extends Controller
{
    public function getList()
    {
        $list = CountryService::getAll();
        return Result::success(['list' => $list]);
    }
}