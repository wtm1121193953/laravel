<?php

namespace App\Modules\Country;


use App\BaseService;

class CountryServer extends BaseService
{
    /**
     * 获取所有国别
     * @return Country[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAll()
    {
        $data = Country::all();
        return $data;
    }
}