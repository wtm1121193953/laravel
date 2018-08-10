<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/tps/getBindInfo', 'TpsBindController@getBindInfo');
Route::post('/tps/bindAccount', 'TpsBindController@bindAccount');
Route::post('/tps/getVcode', 'TpsBindController@getVcode');

