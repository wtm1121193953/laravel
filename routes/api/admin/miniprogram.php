<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/miniprograms', 'MiniprogramController@getList');
Route::get('/miniprograms/all', 'MiniprogramController@getAllList');
Route::post('/miniprogram/add', 'MiniprogramController@add');
Route::post('/miniprogram/edit', 'MiniprogramController@edit');
Route::post('/miniprogram/uploadCert', 'MiniprogramController@uploadCert');