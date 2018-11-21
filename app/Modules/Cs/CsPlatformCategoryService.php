<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/20/020
 * Time: 18:30
 */
namespace App\Modules\Cs;

use App\BaseService;

class CsPlatformCategoryService extends BaseService
{
    public static function getAll()
    {
        return CsPlatformCategory::select('*')->get();
    }
}