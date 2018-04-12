<?php
/**
 * 运营中心接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('user')
    ->namespace('User')
    ->middleware('user')->group(function (){

        Route::post('login', 'LoginController@login');

        Route::get('area/tree', 'AreaController@getTree');

    });