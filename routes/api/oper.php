<?php
/**
 * 运营中心接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('oper')
    ->namespace('Oper')
    ->middleware('oper')->group(function (){

        Route::post('login', 'LoginController@login');

        Route::group([], base_path('routes/api/oper/merchant.php'));
    });