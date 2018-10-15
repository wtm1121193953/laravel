<?php

namespace App\Modules\Country;


use App\BaseService;

class CountryService extends BaseService
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

    /**
     * 根据国别ID获取国别信息
     * @param $countryId
     * @param array $fields
     * @return Country
     */
    public static function getById($countryId, $fields = ['*'])
    {
        $country = Country::select($fields)->find($countryId);
        return $country;
    }

    /**
     * 根据国别ID获取国别信息
     * @param $countryId
     * @return mixed|string
     */
    public static function getNameZhById($countryId)
    {
        $country = self::getById($countryId, ['name_zh']);
        return $country ? $country->name_zh : '';

    }
}