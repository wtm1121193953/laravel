<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::prefix('goods')
    ->group(function(){
        Route::get('/', 'GoodsController@getList');
        Route::post('/add', 'GoodsController@add');
        Route::post('/edit', 'GoodsController@edit');
        Route::post('/changeStatus', 'GoodsController@changeStatus');
        Route::post('/del', 'GoodsController@del');
        Route::post('/changeStock', 'GoodsController@changeStock');
    });