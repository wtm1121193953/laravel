<?php
/**
 * 业务员端接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('bizer')
    ->namespace('Bizer')
    ->middleware('bizer')->group(function (){

        Route::post('login', 'SelfController@login');
        Route::post('logout', 'SelfController@logout');
        Route::post('self/modifyPassword', 'SelfController@modifyPassword');


    });