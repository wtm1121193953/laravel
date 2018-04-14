<?php
/**
 * 运营中心接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('merchant')
    ->namespace('Merchant')
    ->middleware('merchant')->group(function (){

        Route::post('login', 'LoginController@login');

        Route::group([], base_path('routes/api/merchant/goods.php'));
    });